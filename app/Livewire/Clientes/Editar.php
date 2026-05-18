<?php

namespace App\Livewire\Clientes;

use App\Livewire\Forms\ClienteForm;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\User;
use App\Services\NumeracionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'clientes'])]
class Editar extends Component
{
    public ClienteForm $form;

    public ?Cliente $cliente = null;

    public function mount(?Cliente $cliente = null): void
    {
        if ($cliente !== null && $cliente->exists) {
            Gate::authorize('update', $cliente);
            $this->cliente = $cliente;
            $this->form->fromModel($cliente);
        } else {
            Gate::authorize('create', Cliente::class);
            $this->form->activo = true;
            $this->form->codigo_cliente = app(NumeracionService::class)->siguienteNumeroCliente();
        }
    }

    public function deshacer(): void
    {
        if ($this->cliente !== null) {
            $this->form->fromModel($this->cliente);
        } else {
            $this->form->reset();
            $this->form->activo = true;
            $this->form->codigo_cliente = app(NumeracionService::class)->siguienteNumeroCliente();
        }
    }

    public function guardar(): void
    {
        $esNuevo = $this->cliente === null;

        if ($esNuevo) {
            Gate::authorize('create', Cliente::class);
        } else {
            Gate::authorize('update', $this->cliente);
        }

        $cliente = $this->form->save();

        session()->flash('status', $esNuevo
            ? "Cliente «{$cliente->nombre}» creado correctamente."
            : "Cliente «{$cliente->nombre}» actualizado correctamente.");

        $this->redirectRoute('clientes.editar', ['cliente' => $cliente->getKey()]);
    }

    public ?int $confirmarEliminarId = null;

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
        if ($this->cliente === null) {
            return collect();
        }

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
        if ($this->cliente === null) {
            return collect();
        }

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
        $titulo = $this->cliente ? 'Editar cliente' : 'Nuevo cliente';
        $backUrl = $this->cliente
            ? route('clientes.ver', $this->cliente)
            : route('clientes.index');

        return view('livewire.clientes.editar', compact('titulo', 'backUrl'));
    }
}
