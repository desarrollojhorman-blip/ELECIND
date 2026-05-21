<?php

namespace App\Livewire\Usuarios;

use App\Models\Albaran;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'usuarios'])]
#[Title('Ver usuario')]
class Ver extends Component
{
    public User $usuario;

    public ?int $confirmarEliminarId = null;

    public ?string $bloqueadoEliminarMensaje = null;

    public string $ordenProyectos = 'nombre';
    public string $dirProyectos = 'asc';

    public string $ordenAlbaranes = 'fecha';
    public string $dirAlbaranes = 'desc';

    public function mount(User $usuario): void
    {
        Gate::authorize('view', $usuario);
        $this->usuario = $usuario->load('roles', 'cliente');
    }

    public function confirmarEliminar(): void
    {
        $response = Gate::inspect('delete', $this->usuario);

        if (! $response->allowed()) {
            $this->bloqueadoEliminarMensaje = $response->message()
                ?: 'No tienes permiso para eliminar este usuario.';

            return;
        }

        $this->confirmarEliminarId = $this->usuario->id;
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
        Gate::authorize('delete', $this->usuario);
        $username = $this->usuario->username;
        $this->usuario->delete();
        session()->flash('status', "Usuario «{$username}» eliminado correctamente.");
        $this->redirectRoute('usuarios.index', navigate: true);
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
    public function proyectosDelUsuario(): Collection
    {
        $asignados = $this->usuario->proyectos()
            ->with(['cliente:id,nombre', 'tipoProyecto:id,nombre'])
            ->get(['proyectos.id', 'proyectos.nombre', 'proyectos.codigo', 'proyectos.estado', 'proyectos.cliente_id', 'proyectos.tipo_proyecto_id']);

        $responsable = Proyecto::query()
            ->where('responsable_principal_id', $this->usuario->id)
            ->with(['cliente:id,nombre', 'tipoProyecto:id,nombre'])
            ->get(['id', 'nombre', 'codigo', 'estado', 'cliente_id', 'tipo_proyecto_id']);

        $todos = $asignados->merge($responsable)->unique('id')->values();

        $clave = match ($this->ordenProyectos) {
            'codigo'  => fn (Proyecto $p): string => (string) ($p->codigo ?? ''),
            'cliente' => fn (Proyecto $p): string => (string) ($p->cliente?->nombre ?? ''),
            'tipo'    => fn (Proyecto $p): string => (string) ($p->tipoProyecto?->nombre ?? ''),
            'estado'  => fn (Proyecto $p): string => (string) ($p->estado ?? ''),
            default   => fn (Proyecto $p): string => (string) $p->nombre,
        };

        return $this->dirProyectos === 'desc'
            ? $todos->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $todos->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** @return Collection<int, Albaran> */
    #[Computed]
    public function albaranesDelUsuario(): Collection
    {
        $creados = Albaran::query()
            ->where('creado_por', $this->usuario->id)
            ->with(['proyecto:id,nombre', 'cliente:id,nombre'])
            ->get(['id', 'numero', 'fecha', 'estado', 'proyecto_id', 'cliente_id', 'creado_por']);

        $enLineas = Albaran::query()
            ->whereHas('lineasPersonal', fn (Builder $q) => $q->where('trabajador_id', $this->usuario->id))
            ->with(['proyecto:id,nombre', 'cliente:id,nombre'])
            ->get(['id', 'numero', 'fecha', 'estado', 'proyecto_id', 'cliente_id', 'creado_por']);

        $todos = $creados->merge($enLineas)->unique('id')->values();

        $clave = match ($this->ordenAlbaranes) {
            'numero'   => fn (Albaran $a): string => (string) ($a->numero ?? ''),
            'proyecto' => fn (Albaran $a): string => (string) ($a->proyecto?->nombre ?? ''),
            'cliente'  => fn (Albaran $a): string => (string) ($a->cliente?->nombre ?? ''),
            'estado'   => fn (Albaran $a): string => $a->estado instanceof \BackedEnum ? $a->estado->value : (string) $a->estado,
            default    => fn (Albaran $a): string => (string) ($a->fecha ?? ''),
        };

        return $this->dirAlbaranes === 'desc'
            ? $todos->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $todos->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function render(): View
    {
        return view('livewire.usuarios.ver');
    }
}
