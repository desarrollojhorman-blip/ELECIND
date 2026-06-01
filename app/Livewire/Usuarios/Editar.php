<?php

namespace App\Livewire\Usuarios;

use App\Livewire\Forms\UserForm;
use App\Models\Albaran;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'usuarios'])]
class Editar extends Component
{
    public UserForm $form;

    public ?User $usuario = null;

    public bool $mostrarPassword = false;

    public int $passwordRenderKey = 0;

    public bool $usernameTocadoManual = false;

    public bool $bypassDuplicados = false;

    /** @var array<int, array{campo: string, valor: string, usuario_id: int, usuario_nombre: string, eliminado: bool}> */
    public array $duplicadosEncontrados = [];

    public bool $modalDuplicadosAbierto = false;

    public ?int $confirmarEliminarId = null;

    public ?string $bloqueadoEliminarMensaje = null;

    public string $ordenProyectos = 'nombre';
    public string $dirProyectos = 'asc';

    public string $ordenAlbaranes = 'fecha';
    public string $dirAlbaranes = 'desc';

    public function mount(?User $usuario = null): void
    {
        if ($usuario !== null && $usuario->exists) {
            Gate::authorize('update', $usuario);
            $this->usuario = $usuario;
            $this->form->fromModel($usuario);
            $this->usernameTocadoManual = true;
        } else {
            Gate::authorize('create', User::class);
            $this->form->activo = true;
            $this->form->tipo_usuario = 'interno';
            $this->form->rol = $this->primerRolDisponible();
        }
    }

    public function deshacer(): void
    {
        if ($this->usuario !== null) {
            $this->form->fromModel($this->usuario);
        } else {
            $this->form->reset();
            $this->form->activo = true;
            $this->form->tipo_usuario = 'interno';
            $this->form->rol = $this->primerRolDisponible();
            $this->usernameTocadoManual = false;
        }

        $this->mostrarPassword = false;
        $this->passwordRenderKey++;
        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->resetErrorBag();
    }

    public function guardar(): void
    {
        $esNuevo = $this->usuario === null;

        if ($esNuevo) {
            Gate::authorize('create', User::class);
        } else {
            Gate::authorize('update', $this->usuario);
        }

        if (! Gate::forUser(auth()->user())->allows('puedeAsignarRol', [User::class, $this->form->rol])) {
            $this->addError('form.rol', 'No tienes permiso para asignar este rol.');

            return;
        }

        if (! $this->bypassDuplicados) {
            $this->form->validate();

            $duplicados = $this->form->buscarDuplicados();
            if (count($duplicados) > 0) {
                $this->duplicadosEncontrados = $duplicados;
                $this->modalDuplicadosAbierto = true;

                return;
            }
        }

        $usuario = $this->form->save();

        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->modalDuplicadosAbierto = false;
        $this->mostrarPassword = false;
        $this->passwordRenderKey++;

        session()->flash('status', $esNuevo
            ? "Usuario «{$usuario->username}» creado correctamente."
            : "Usuario «{$usuario->username}» actualizado correctamente.");

        $this->redirectRoute('usuarios.editar', ['usuario' => $usuario->getKey()], navigate: false);
    }

    /* ── Duplicados ─────────────────────────────────────────────────────── */

    public function confirmarCrearAunqueDuplicado(): void
    {
        $this->bypassDuplicados = true;
        $this->modalDuplicadosAbierto = false;
        $this->guardar();
    }

    public function cancelarDuplicados(): void
    {
        $this->modalDuplicadosAbierto = false;
        $this->duplicadosEncontrados = [];
    }

    public function usarExistente(int $id): void
    {
        $this->redirectRoute('usuarios.editar', ['usuario' => $id], navigate: true);
    }

    /* ── Username auto-sugerencia ────────────────────────────────────────── */

    public function updatedFormNombre(): void
    {
        $this->actualizarSugerenciaUsername();
    }

    public function updatedFormApellidos(): void
    {
        $this->actualizarSugerenciaUsername();
    }

    public function updatedFormUsername(): void
    {
        if ($this->form->id !== null) {
            return;
        }

        $sugerido = UserForm::sugerirUsername($this->form->nombre, $this->form->apellidos);
        if ($this->form->username !== $sugerido) {
            $this->usernameTocadoManual = true;
        }
    }

    private function actualizarSugerenciaUsername(): void
    {
        if ($this->form->id !== null || $this->usernameTocadoManual) {
            return;
        }

        $this->form->username = UserForm::sugerirUsername($this->form->nombre, $this->form->apellidos);
    }

    /* ── Password ────────────────────────────────────────────────────────── */

    public function generarPasswordSegura(): void
    {
        $this->form->password = Str::password(14, true, true, false, false);
        $this->mostrarPassword = true;
        $this->passwordRenderKey++;
        $this->resetValidation(['form.password']);
    }

    public function toggleMostrarPassword(): void
    {
        $this->mostrarPassword = ! $this->mostrarPassword;
        $this->passwordRenderKey++;
    }

    /* ── Eliminar ────────────────────────────────────────────────────────── */

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
        $this->redirectRoute('usuarios.index', navigate: false);
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

    /* ── Computeds ───────────────────────────────────────────────────────── */

    /** @return Collection<int, Role> */
    #[Computed]
    public function rolesDisponibles(): Collection
    {
        /** @var User $actual */
        $actual = auth()->user();
        $nivelActual = $actual->nivelMaximo();

        return Role::query()
            ->where('nivel', '<=', $nivelActual)
            ->orderByDesc('nivel')
            ->get()
            ->filter(fn (Role $rol): bool => Gate::forUser($actual)->allows('puedeAsignarRol', [User::class, $rol->name]))
            ->values();
    }

    #[Computed]
    public function accesoRolSeleccionado(): string
    {
        /** @var Role|null $rol */
        $rol = $this->rolesDisponibles()->firstWhere('name', $this->form->rol);

        return $rol?->acceso ?? '—';
    }

    #[Computed]
    public function rolTieneScoping(): bool
    {
        return (bool) Role::firstWhere('name', $this->form->rol)?->getAttribute('solo_clientes_asignados');
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function empresasDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo_cliente', 'nombre']);
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDelUsuario(): Collection
    {
        if ($this->usuario === null) {
            return collect();
        }

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
        if ($this->usuario === null) {
            return collect();
        }

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

    private function primerRolDisponible(): string
    {
        $rol = $this->rolesDisponibles()->first();

        return $rol instanceof Role ? $rol->name : 'trabajador';
    }

    public function render(): View
    {
        $titulo = $this->usuario ? 'Editar usuario' : 'Nuevo usuario';

        return view('livewire.usuarios.editar', compact('titulo'));
    }
}
