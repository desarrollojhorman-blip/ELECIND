<?php

namespace App\Livewire\Materiales\NumeroPedidos;

use App\Models\NumeroPedido;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Listado de Pedidos. Crear/Editar/Ver se gestionan en páginas dedicadas
 * (Pedidos\Editar, Pedidos\Ver). Este componente solo lista y permite
 * eliminar/restaurar.
 */
#[Layout('components.layouts.web', ['active' => 'pedidos'])]
#[Title('Pedidos')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', NumeroPedido::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function limpiarBuscador(): void
    {
        $this->buscar = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['numero', 'fecha', 'proveedor'];
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

    public function confirmarEliminar(int $id): void
    {
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::findOrFail($id);
        Gate::authorize('delete', $pedido);
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::findOrFail($id);
        Gate::authorize('delete', $pedido);

        $pedido->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Pedido «{$pedido->numero}» eliminado correctamente.");
    }

    public function restaurar(int $id): void
    {
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $pedido);

        $pedido->restore();

        session()->flash('status', "Pedido «{$pedido->numero}» restaurado.");
    }

    public function render(): View
    {
        $query = NumeroPedido::query()->withCount('materiales');

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('numero', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino)
                    ->orWhere('proveedor', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.materiales.numero-pedidos.index', [
            'pedidos' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
