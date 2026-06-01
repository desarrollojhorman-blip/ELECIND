<?php

namespace App\Livewire\Usuarios;

use App\Models\Role;
use App\Models\User;
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

#[Layout('components.layouts.web', ['active' => 'usuarios'])]
#[Title('Usuarios')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: todos | activos | inactivos | papelera */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'activos';

    /** interno | externo | null */
    #[Url(as: 'tipo')]
    public ?string $filtroTipo = null;

    /** Nombre del rol (superadmin/administrador/trabajador/responsable) */
    #[Url(as: 'rol')]
    public ?string $filtroRol = null;

    #[Url(as: 'empresa')]
    public string $filtroEmpresaCliente = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    /**
     * Modo "Papelera" → muestra SOLO los usuarios eliminados (soft-deleted).
     * Solo aplicable a quien tenga `usuarios.gestionar_papelera`.
     */
    #[Url(as: 'papelera')]
    public bool $verPapelera = false;

    public bool $panelFiltrosAbierto = false;

    public ?int $confirmarEliminarId = null;

    /**
     * Si Gate::inspect('delete', ...) deniega (sin permiso, jerarquía o
     * dependencias), el mensaje del Policy llega aquí y muestra modal informativo.
     */
    public ?string $bloqueadoEliminarMensaje = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);

        // Defensa: si manipulan la URL ?papelera=1 sin permiso, lo ignoramos.
        if ($this->verPapelera && ! $this->puedeVerPapelera) {
            $this->verPapelera = false;
        }
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroRol(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEmpresaCliente(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function updatedVerPapelera(): void
    {
        if ($this->verPapelera && ! $this->puedeVerPapelera) {
            $this->verPapelera = false;

            return;
        }
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = 'activos';
        $this->filtroTipo = null;
        $this->filtroRol = null;
        $this->filtroEmpresaCliente = '';
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
        $this->filtroEstado = 'activos';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroTipo(): void
    {
        $this->filtroTipo = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroRol(): void
    {
        $this->filtroRol = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroEmpresaCliente(): void
    {
        $this->filtroEmpresaCliente = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['id', 'username', 'nombre', 'email', 'tipo_usuario', 'created_at', 'rol'];
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

    /* ───────────────────────── Eliminar / restaurar ────────────────────── */

    public function confirmarEliminar(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::withTrashed()->findOrFail($id);

        $response = Gate::inspect('delete', $usuario);

        if (! $response->allowed()) {
            $this->bloqueadoEliminarMensaje = $response->message()
                ?: 'No tienes permiso para eliminar este usuario.';

            return;
        }

        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function cerrarBloqueo(): void
    {
        $this->bloqueadoEliminarMensaje = null;
    }

    public function eliminar(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::findOrFail($id);
        // Defensa server-side: si llegamos aquí saltándose la UI, el Policy
        // bloquea con AuthorizationException + mensaje claro.
        Gate::authorize('delete', $usuario);

        $usuario->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Usuario «{$usuario->username}» eliminado correctamente.");
    }

    public function restaurar(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $usuario);

        $usuario->restore();

        session()->flash('status', "Usuario «{$usuario->username}» restaurado.");
    }

    /* ── Exportar (filtros + orden actuales del listado) ─────────── */

    /**
     * Parámetros de URL para los exports: filtros y orden vigentes EN ESTE
     * INSTANTE. Construirlos en PHP elimina dependencia del estado del DOM.
     *
     * @return array<string, string>
     */
    private function paramsExport(): array
    {
        return [
            'q' => $this->buscar,
            'estado' => $this->filtroEstado,
            'tipo' => $this->filtroTipo ?? '',
            'rol' => $this->filtroRol ?? '',
            'empresa' => $this->filtroEmpresaCliente,
            'orden' => $this->ordenColumna,
            'dir' => $this->ordenDireccion,
        ];
    }

    public function exportarExcel(): void
    {
        Gate::authorize('usuarios.exportar');

        $this->dispatch(
            'descargar',
            url: route('usuarios.exportar.excel', $this->paramsExport())
        );
    }

    public function exportarPdf(string $orientacion): void
    {
        Gate::authorize('usuarios.exportar');

        if (! in_array($orientacion, ['vertical', 'horizontal'], true)) {
            abort(404);
        }

        $this->dispatch(
            'descargar',
            url: route('usuarios.exportar.pdf', array_merge(['orientacion' => $orientacion], $this->paramsExport()))
        );
    }

    /* ───────────────────────── Computeds y catálogos ───────────────────── */

    /**
     * Puede ver/gestionar la papelera de usuarios. Protegido por permiso
     * `usuarios.gestionar_papelera`. Por defecto solo el superadmin.
     */
    #[Computed]
    public function puedeVerPapelera(): bool
    {
        return auth()->user()?->can('usuarios.gestionar_papelera') ?? false;
    }

    /**
     * Total de usuarios "vivos" (activos + inactivos, EXCLUYE papelera).
     * Es el número junto al título; NO cambia entre modos.
     */
    #[Computed]
    public function totalUsuarios(): int
    {
        return User::count();
    }

    /** Total en papelera — para el badge del checkbox del superadmin. */
    #[Computed]
    public function totalPapelera(): int
    {
        return User::onlyTrashed()->count();
    }

    /** Subtítulo bajo el título: total real (activos + inactivos, sin papelera). */
    #[Computed]
    public function subtituloListado(): string
    {
        $total = $this->totalUsuarios;

        return $total === 1
            ? '1 usuario · activo o inactivo'
            : "{$total} usuarios · activos e inactivos";
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroEstado !== 'activos') {
            $count++;
        }
        if ($this->filtroTipo !== null) {
            $count++;
        }
        if ($this->filtroRol !== null) {
            $count++;
        }
        if (trim($this->filtroEmpresaCliente) !== '') {
            $count++;
        }

        return $count;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    /**
     * Roles que el usuario actual puede asignar (según jerarquía + permisos).
     * Usado por el filtro de Rol del listado.
     *
     * @return Collection<int, Role>
     */
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

    /* ───────────────────────── Render ───────────────────────────────────── */

    public function render(): View
    {
        /** @var User $actual */
        $actual = auth()->user();
        $nivelActual = $actual->nivelMaximo();

        // Modo papelera: SOLO trashed, ignora filtro Estado (los demás siguen).
        $modoPapelera = $this->verPapelera && $this->puedeVerPapelera;

        $query = $modoPapelera
            ? User::onlyTrashed()
            : User::query();

        $query->with(['roles:id,name,nivel,acceso', 'cliente:id,codigo_cliente,nombre']);

        // Jerarquía: oculta usuarios con algún rol de nivel mayor al propio.
        $query->whereDoesntHave('roles', function (Builder $q) use ($nivelActual): void {
            $q->where('nivel', '>', $nivelActual);
        });

        if (! $modoPapelera) {
            // Estado: activos / inactivos / (vacío = ambos). La opción "papelera"
            // ya no se aplica desde la UI; si llega por URL antigua, se ignora.
            if ($this->filtroEstado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtroEstado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        if ($this->filtroTipo !== null) {
            $query->where('tipo_usuario', $this->filtroTipo);
        }

        if ($this->filtroRol !== null) {
            $rol = $this->filtroRol;
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $rol));
        }

        if (trim($this->filtroEmpresaCliente) !== '') {
            $terminoCliente = '%'.trim($this->filtroEmpresaCliente).'%';
            $query->whereHas('cliente', fn (Builder $q) => $q->where('nombre', 'like', $terminoCliente));
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('username', 'like', $termino)
                    ->orWhere('nombre', 'like', $termino)
                    ->orWhere('apellidos', 'like', $termino)
                    ->orWhere('email', 'like', $termino)
                    ->orWhere('dni', 'like', $termino);
            });
        }

        if ($this->ordenColumna === 'rol') {
            $query->select('users.*')
                ->leftJoin('model_has_roles', function ($join): void {
                    $join->on('model_has_roles.model_id', '=', 'users.id')
                         ->where('model_has_roles.model_type', '=', User::class);
                })
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->orderBy('roles.nivel', $this->ordenDireccion)
                ->orderBy('roles.name', $this->ordenDireccion);
        } else {
            $query->orderBy($this->ordenColumna, $this->ordenDireccion);
        }

        return view('livewire.usuarios.index', [
            'usuarios' => $query->paginate($this->porPagina)->onEachSide(2),
            'modoPapelera' => $modoPapelera,
        ]);
    }
}
