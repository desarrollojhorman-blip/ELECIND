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
            $this->form->codigo_cliente = (string) app(NumeracionService::class)->siguienteNumeroCliente();
        }
    }

    public function deshacer(): void
    {
        if ($this->cliente !== null) {
            $this->form->fromModel($this->cliente);
        } else {
            $this->form->reset();
            $this->form->activo = true;
            $this->form->codigo_cliente = (string) app(NumeracionService::class)->siguienteNumeroCliente();
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

        // Al crear: aviso amigable si el código ya existe (con fecha de creación).
        // Al editar el código es inmutable, no procede comprobarlo.
        if ($esNuevo) {
            $existente = Cliente::query()
                ->where('codigo_cliente', $this->form->codigo_cliente)
                ->first();

            if ($existente !== null) {
                $this->addError(
                    'form.codigo_cliente',
                    "El código {$this->form->codigo_cliente} ya existe (creado {$existente->created_at->format('d/m/y H:i')}). Escribe otro número."
                );

                return;
            }
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

    /* ── Ordenación de tablas vinculadas ───────────────────────── */

    public string $ordenProyectos = 'nombre';
    public string $dirProyectos   = 'asc';
    public string $ordenUsuarios  = 'nombre';
    public string $dirUsuarios    = 'asc';

    public function ordenarProyectos(string $campo): void
    {
        if ($this->ordenProyectos === $campo) {
            $this->dirProyectos = $this->dirProyectos === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenProyectos = $campo;
            $this->dirProyectos = 'asc';
        }
    }

    public function ordenarUsuarios(string $campo): void
    {
        if ($this->ordenUsuarios === $campo) {
            $this->dirUsuarios = $this->dirUsuarios === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenUsuarios = $campo;
            $this->dirUsuarios = 'asc';
        }
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDelCliente(): Collection
    {
        if ($this->cliente === null) {
            return collect();
        }

        $proyectos = Proyecto::query()
            ->where('cliente_id', $this->cliente->id)
            ->with('tipoProyecto')
            ->get(['id', 'nombre', 'codigo', 'estado', 'tipo_proyecto_id']);

        $clave = match ($this->ordenProyectos) {
            'codigo' => fn (Proyecto $p): string => (string) $p->codigo,
            'tipo'   => fn (Proyecto $p): string => (string) ($p->tipoProyecto?->nombre ?? ''),
            'estado' => fn (Proyecto $p): string => (string) ($p->estado ?? ''),
            default  => fn (Proyecto $p): string => (string) $p->nombre,
        };

        return $this->dirProyectos === 'desc'
            ? $proyectos->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $proyectos->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function usuariosDeLosProyectos(): Collection
    {
        if ($this->cliente === null) {
            return collect();
        }

        $usuarios = Proyecto::query()
            ->where('cliente_id', $this->cliente->id)
            ->with(['usuarios:id,nombre,apellidos,email,activo', 'usuarios.roles'])
            ->get()
            ->flatMap(fn (Proyecto $p) => $p->usuarios)
            ->unique('id')
            ->values();

        $clave = match ($this->ordenUsuarios) {
            'email'  => fn (User $u): string => (string) ($u->email ?? ''),
            'rol'    => fn (User $u): string => $u->getRoleNames()->join(', '),
            'estado' => fn (User $u): int => $u->activo ? 1 : 0,
            default  => fn (User $u): string => trim($u->nombre.' '.$u->apellidos),
        };

        return $this->dirUsuarios === 'desc'
            ? $usuarios->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $usuarios->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
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
