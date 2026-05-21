<?php

namespace App\Livewire\Clientes;

use App\Models\Albaran;
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

    public ?string $bloqueadoEliminarMensaje = null;

    public string $ordenProyectos = 'nombre';
    public string $dirProyectos = 'asc';

    public string $ordenUsuarios = 'nombre';
    public string $dirUsuarios = 'asc';

    public string $ordenAlbaranes = 'fecha';
    public string $dirAlbaranes = 'desc';

    public function mount(Cliente $cliente): void
    {
        Gate::authorize('view', $cliente);
        $this->cliente = $cliente;
    }

    public function confirmarEliminar(): void
    {
        $response = Gate::inspect('delete', $this->cliente);

        if (! $response->allowed()) {
            $this->bloqueadoEliminarMensaje = $response->message()
                ?: 'No tienes permiso para eliminar este cliente.';

            return;
        }

        $this->confirmarEliminarId = $this->cliente->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function cerrarBloqueo(): void
    {
        $this->bloqueadoEliminarMensaje = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->cliente);
        $nombre = $this->cliente->nombre;
        $this->cliente->delete();
        session()->flash('status', "Cliente «{$nombre}» eliminado correctamente.");
        $this->redirectRoute('clientes.index', navigate: true);
    }

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

    public function ordenarAlbaranes(string $campo): void
    {
        if ($this->ordenAlbaranes === $campo) {
            $this->dirAlbaranes = $this->dirAlbaranes === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenAlbaranes = $campo;
            $this->dirAlbaranes = 'asc';
        }
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDelCliente(): Collection
    {
        $proyectos = Proyecto::query()
            ->where('cliente_id', $this->cliente->id)
            ->with('tipoProyecto')
            ->get(['id', 'nombre', 'codigo', 'estado', 'tipo_proyecto_id']);

        $clave = match ($this->ordenProyectos) {
            'codigo'  => fn (Proyecto $p): string => (string) ($p->codigo ?? ''),
            'tipo'    => fn (Proyecto $p): string => (string) ($p->tipoProyecto?->nombre ?? ''),
            'estado'  => fn (Proyecto $p): string => (string) ($p->estado ?? ''),
            default   => fn (Proyecto $p): string => (string) $p->nombre,
        };

        return $this->dirProyectos === 'desc'
            ? $proyectos->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $proyectos->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function usuariosDeLosProyectos(): Collection
    {
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
            'estado' => fn (User $u): int    => $u->activo ? 1 : 0,
            default  => fn (User $u): string => trim($u->nombre.' '.$u->apellidos),
        };

        return $this->dirUsuarios === 'desc'
            ? $usuarios->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $usuarios->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** @return Collection<int, Albaran> */
    #[Computed]
    public function albaranesDelCliente(): Collection
    {
        $albaranes = Albaran::query()
            ->where('cliente_id', $this->cliente->id)
            ->with(['proyecto:id,nombre'])
            ->get(['id', 'numero', 'fecha', 'estado', 'proyecto_id', 'cliente_id', 'creado_por']);

        $clave = match ($this->ordenAlbaranes) {
            'numero'   => fn (Albaran $a): string => (string) ($a->numero ?? ''),
            'proyecto' => fn (Albaran $a): string => (string) ($a->proyecto?->nombre ?? ''),
            'estado'   => fn (Albaran $a): string => $a->estado instanceof \BackedEnum ? $a->estado->value : (string) $a->estado,
            default    => fn (Albaran $a): string => (string) ($a->fecha ?? ''),
        };

        return $this->dirAlbaranes === 'desc'
            ? $albaranes->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $albaranes->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function render(): View
    {
        return view('livewire.clientes.ver');
    }
}
