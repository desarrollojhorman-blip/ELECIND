<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'clientes'])]
class Ver extends Component
{
    public Cliente $cliente;

    public ?int $confirmarEliminarId = null;

    public function mount(Cliente $cliente): void
    {
        Gate::authorize('view', $cliente);
        $this->cliente = $cliente;
    }

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->cliente);
        $this->confirmarEliminarId = $this->cliente->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->cliente);
        $nombre = $this->cliente->nombre;
        $this->cliente->delete();
        session()->flash('status', "Cliente «{$nombre}» enviado a papelera.");
        $this->redirectRoute('clientes.index', navigate: true);
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDelCliente(): Collection
    {
        return Proyecto::query()
            ->where('cliente_id', $this->cliente->id)
            ->with('tipoProyecto')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'estado', 'tipo_proyecto_id']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function usuariosDeLosProyectos(): Collection
    {
        return Proyecto::query()
            ->where('cliente_id', $this->cliente->id)
            ->with(['usuarios:id,nombre,apellidos,email,activo', 'usuarios.roles'])
            ->get()
            ->flatMap(fn (Proyecto $p) => $p->usuarios)
            ->unique('id')
            ->sortBy(fn ($u) => trim($u->nombre.' '.$u->apellidos))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.clientes.ver');
    }
}
