<?php

namespace App\Livewire\Materiales;

use App\Livewire\Forms\MaterialForm;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'materiales'])]
#[Title('Materiales')]
class Index extends Component
{
    use WithPagination;

    public MaterialForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: todos | activos | inactivos | papelera */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'grupo')]
    public string $filtroGrupo = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Material::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroGrupo(): void
    {
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = 'todos';
        $this->filtroGrupo = '';
        $this->buscar = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function limpiarBuscador(): void
    {
        $this->buscar = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroEstado(): void
    {
        $this->filtroEstado = 'todos';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroGrupo(): void
    {
        $this->filtroGrupo = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['nombre', 'codigo', 'grupo', 'activo', 'created_at'];
        if (! \in_array($columna, $columnasPermitidas, true)) {
            return;
        }

        if ($this->ordenColumna === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenColumna = $columna;
            $this->ordenDireccion = 'asc';
        }
    }

    public function abrirCrear(): void
    {
        Gate::authorize('create', Material::class);

        $this->form->reset();
        $this->form->unidad_medida = 'ud';
        $this->form->notificar_stock_bajo = true;
        $this->form->activo = true;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var Material $material */
        $material = Material::withTrashed()->findOrFail($id);

        Gate::authorize('update', $material);

        $this->form->fromModel($material);
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', Material::class);
        } else {
            /** @var Material $existente */
            $existente = Material::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $material = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Material «{$material->nombre}» creado correctamente."
            : "Material «{$material->nombre}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->form->reset();
        $this->resetErrorBag();
    }

    public function confirmarEliminar(int $id): void
    {
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        /** @var Material $material */
        $material = Material::findOrFail($id);
        Gate::authorize('delete', $material);

        $material->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Material «{$material->nombre}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var Material $material */
        $material = Material::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $material);

        $material->restore();

        session()->flash('status', "Material «{$material->nombre}» restaurado.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroEstado !== 'todos') {
            $count++;
        }
        if ($this->filtroGrupo !== '') {
            $count++;
        }

        return $count;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function gruposDisponibles(): Collection
    {
        return Material::query()
            ->withTrashed()
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->distinct()
            ->orderBy('grupo')
            ->pluck('grupo');
    }

    public function render(): View
    {
        $query = Material::query()->withSum('lotes as stock_total', 'stock_disponible');

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivos') {
            $query->where('activo', false);
        }

        if ($this->filtroGrupo !== '') {
            $query->where('grupo', $this->filtroGrupo);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('codigo', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino)
                    ->orWhere('grupo', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.materiales.index', [
            'materiales' => $query->paginate(15),
        ]);
    }
}
