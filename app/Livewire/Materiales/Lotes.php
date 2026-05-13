<?php

namespace App\Livewire\Materiales;

use App\Livewire\Forms\MaterialLoteForm;
use App\Models\Material;
use App\Models\MaterialLote;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'materiales'])]
#[Title('Lotes del material')]
class Lotes extends Component
{
    use WithPagination;

    public Material $material;

    public MaterialLoteForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: todos | con_stock | sin_stock | papelera */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'fecha_entrada';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(Material $material): void
    {
        Gate::authorize('viewAny', MaterialLote::class);

        $this->material = $material;
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
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

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['codigo_lote', 'proveedor', 'fecha_entrada', 'fecha_caducidad', 'stock_disponible', 'created_at'];
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
        Gate::authorize('create', MaterialLote::class);

        $this->form->reset();
        $this->form->material_id = (int) $this->material->getKey();
        $this->form->fecha_entrada = now()->format('Y-m-d');
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var MaterialLote $lote */
        $lote = MaterialLote::withTrashed()->findOrFail($id);

        Gate::authorize('update', $lote);

        $this->form->fromModel($lote);
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', MaterialLote::class);
            $this->form->material_id = (int) $this->material->getKey();
        } else {
            /** @var MaterialLote $existente */
            $existente = MaterialLote::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $lote = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Lote «{$lote->codigo_lote}» creado correctamente."
            : "Lote «{$lote->codigo_lote}» actualizado correctamente.");
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
        /** @var MaterialLote $lote */
        $lote = MaterialLote::findOrFail($id);
        Gate::authorize('delete', $lote);

        $lote->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Lote «{$lote->codigo_lote}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var MaterialLote $lote */
        $lote = MaterialLote::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $lote);

        $lote->restore();

        session()->flash('status', "Lote «{$lote->codigo_lote}» restaurado.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return $this->filtroEstado !== 'todos' ? 1 : 0;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    #[Computed]
    public function stockTotal(): float
    {
        return (float) $this->material->lotes()->sum('stock_disponible');
    }

    public function render(): View
    {
        $query = MaterialLote::query()->where('material_id', $this->material->getKey());

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'con_stock') {
            $query->where('stock_disponible', '>', 0);
        } elseif ($this->filtroEstado === 'sin_stock') {
            $query->where('stock_disponible', '<=', 0);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('codigo_lote', 'like', $termino)
                    ->orWhere('proveedor', 'like', $termino)
                    ->orWhere('n_pedido', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.materiales.lotes', [
            'lotes' => $query->paginate(15),
        ]);
    }
}
