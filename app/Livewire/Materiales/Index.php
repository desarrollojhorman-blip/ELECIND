<?php

namespace App\Livewire\Materiales;

use App\Livewire\Forms\MaterialForm;
use App\Models\FamiliaMaterial;
use App\Models\Material;
use App\Models\NumeroPedido;
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

#[Layout('components.layouts.web', ['active' => 'materiales_lista'])]
#[Title('Materiales')]
class Index extends Component
{
    use WithPagination;

    public MaterialForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'pedido')]
    public string $filtroPedido = '';

    #[Url(as: 'familia')]
    public string $filtroFamilia = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public bool $modoSoloLectura = false;

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

    public function updatedFiltroPedido(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroFamilia(): void
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
        $this->filtroPedido = '';
        $this->filtroFamilia = '';
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

    public function quitarFiltroPedido(): void
    {
        $this->filtroPedido = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroFamilia(): void
    {
        $this->filtroFamilia = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['id', 'descripcion', 'unidad_medida', 'stock', 'numero_pedido_id', 'familia_id'];
        if (! \in_array($columna, $permitidas, true)) {
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

        if ($this->filtroPedido !== '') {
            $this->form->numero_pedido_id = (int) $this->filtroPedido;
        }

        $this->resetErrorBag();
        $this->modoSoloLectura = false;
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        $material = Material::withTrashed()->findOrFail($id);
        Gate::authorize('view', $material);

        $this->form->fromModel($material);
        $this->resetErrorBag();
        $this->modoSoloLectura = true;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        $material = Material::withTrashed()->findOrFail($id);
        Gate::authorize('update', $material);

        $this->form->fromModel($material);
        $this->resetErrorBag();
        $this->modoSoloLectura = false;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        if ($this->modoSoloLectura) {
            abort(403);
        }

        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', Material::class);
        } else {
            $existente = Material::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $material = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Material «{$material->descripcion}» creado correctamente."
            : "Material «{$material->descripcion}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modoSoloLectura = false;
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
        $material = Material::findOrFail($id);
        Gate::authorize('delete', $material);

        $material->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Material «{$material->descripcion}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        $material = Material::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $material);

        $material->restore();

        session()->flash('status', "Material «{$material->descripcion}» restaurado.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return ($this->filtroPedido !== '' ? 1 : 0) + ($this->filtroFamilia !== '' ? 1 : 0);
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    #[Computed]
    public function pedidosDisponibles(): Collection
    {
        return NumeroPedido::query()
            ->orderBy('fecha', 'desc')
            ->orderBy('numero')
            ->get(['id', 'numero', 'proveedor']);
    }

    #[Computed]
    public function familiasDisponibles(): Collection
    {
        return FamiliaMaterial::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    public function render(): View
    {
        $query = Material::query()->with(['numeroPedido', 'familia']);

        if ($this->filtroPedido !== '') {
            $query->where('numero_pedido_id', $this->filtroPedido);
        }

        if ($this->filtroFamilia !== '') {
            if ($this->filtroFamilia === 'sin_familia') {
                $query->whereNull('familia_id');
            } else {
                $query->where('familia_id', $this->filtroFamilia);
            }
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('descripcion', 'like', $termino)
                    ->orWhere('unidad_medida', 'like', $termino)
                    ->orWhereHas('numeroPedido', fn ($q2) => $q2->where('numero', 'like', $termino))
                    ->orWhereHas('familia', fn ($q3) => $q3->where('nombre', 'like', $termino));
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.materiales.index', [
            'materiales' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
