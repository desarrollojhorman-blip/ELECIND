<?php

namespace App\Livewire\Conceptos;

use App\Livewire\Forms\ConceptoForm;
use App\Models\Concepto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'conceptos'])]
#[Title('Conceptos')]
class Index extends Component
{
    use WithPagination;

    public ConceptoForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: activos | inactivos | todos | papelera */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'activos';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Concepto::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = 'activos';
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
        $this->filtroEstado = 'activos';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['nombre', 'activo', 'created_at'];
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
        Gate::authorize('create', Concepto::class);

        $this->form->reset();
        $this->form->activo = true;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::withTrashed()->findOrFail($id);

        Gate::authorize('update', $concepto);

        $this->form->fromModel($concepto);
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', Concepto::class);
        } else {
            /** @var Concepto $existente */
            $existente = Concepto::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $concepto = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Concepto «{$concepto->nombre}» creado correctamente."
            : "Concepto «{$concepto->nombre}» actualizado correctamente.");
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
        /** @var Concepto $concepto */
        $concepto = Concepto::findOrFail($id);
        Gate::authorize('delete', $concepto);

        $concepto->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Concepto «{$concepto->nombre}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $concepto);

        $concepto->restore();

        session()->flash('status', "Concepto «{$concepto->nombre}» restaurado.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return $this->filtroEstado !== 'activos' ? 1 : 0;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    public function render(): View
    {
        $query = Concepto::query()->withCount('proyectos');

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivos') {
            $query->where('activo', false);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.conceptos.index', [
            'conceptos' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
