<?php

namespace App\Livewire\Pedidos;

use App\Models\Material;
use App\Models\NumeroPedido;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'pedidos'])]
class Ver extends Component
{
    public NumeroPedido $pedido;

    public ?int $confirmarEliminarId = null;

    public function mount(NumeroPedido $pedido): void
    {
        Gate::authorize('view', $pedido);
        $this->pedido = $pedido;
    }

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->pedido);
        $this->confirmarEliminarId = $this->pedido->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->pedido);

        $numero = $this->pedido->numero;
        $this->pedido->delete();
        session()->flash('status', "Pedido «{$numero}» enviado a papelera.");
        $this->redirectRoute('materiales.pedidos', navigate: true);
    }

    /** @return EloquentCollection<int, Material> */
    #[Computed]
    public function materialesDelPedido(): EloquentCollection
    {
        return Material::query()
            ->where('numero_pedido_id', $this->pedido->id)
            ->with('familia:id,nombre')
            ->orderBy('descripcion')
            ->get();
    }

    /** @return EloquentCollection<int, Material> */
    #[Computed]
    public function materialesConConsumo(): EloquentCollection
    {
        return Material::query()
            ->where('numero_pedido_id', $this->pedido->id)
            ->withSum(['lineasAlbaran as cantidad_consumida' => function ($q): void {
                $q->whereHas('albaran', fn ($q2) => $q2->whereNull('deleted_at'));
            }], 'cantidad')
            ->orderBy('descripcion')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pedidos.ver');
    }
}
