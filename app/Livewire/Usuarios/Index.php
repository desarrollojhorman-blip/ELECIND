<?php

namespace App\Livewire\Usuarios;

use App\Livewire\Forms\UserForm;
use App\Models\Cliente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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

    public UserForm $form;

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
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    /**
     * Modo "Papelera" → muestra SOLO los usuarios eliminados (soft-deleted).
     * Solo aplicable a quien tenga `usuarios.gestionar_papelera`.
     */
    #[Url(as: 'papelera')]
    public bool $verPapelera = false;

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    /**
     * Si Gate::inspect('delete', ...) deniega (sin permiso, jerarquía o
     * dependencias), el mensaje del Policy llega aquí y muestra modal informativo.
     */
    public ?string $bloqueadoEliminarMensaje = null;

    /** @var array<int, array{campo: string, valor: string, usuario_id: int, usuario_nombre: string, eliminado: bool}> */
    public array $duplicadosEncontrados = [];

    public bool $modalDuplicadosAbierto = false;

    public bool $bypassDuplicados = false;

    public bool $usernameTocadoManual = false;

    public bool $mostrarPassword = false;

    public int $passwordRenderKey = 0;

    public int $resetKey = 0;

    public bool $modoSoloLectura = false;

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
        $columnasPermitidas = ['id', 'username', 'nombre', 'email', 'tipo_usuario', 'created_at'];
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

    /* ───────────────────────── Modal alta / edición ────────────────────── */

    public function abrirCrear(): void
    {
        Gate::authorize('create', User::class);

        $this->form->reset();
        $this->form->activo = true;
        $this->form->tipo_usuario = 'interno';
        $this->form->rol = $this->primerRolDisponible();
        $this->usernameTocadoManual = false;
        $this->mostrarPassword = false;
        $this->passwordRenderKey++;
        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::withTrashed()->findOrFail($id);

        Gate::authorize('view', $usuario);

        $this->form->fromModel($usuario);
        $this->usernameTocadoManual = true;
        $this->mostrarPassword = false;
        $this->passwordRenderKey++;
        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->modoSoloLectura = true;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::withTrashed()->findOrFail($id);

        Gate::authorize('update', $usuario);

        $this->form->fromModel($usuario);
        $this->usernameTocadoManual = true;
        $this->mostrarPassword = false;
        $this->passwordRenderKey++;
        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->modoSoloLectura = false;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', User::class);
        } else {
            /** @var User $existente */
            $existente = User::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
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

        $this->modalAbierto = false;
        $this->modalDuplicadosAbierto = false;
        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->form->reset();
        $this->usernameTocadoManual = false;
        $this->passwordRenderKey++;

        session()->flash('status', $esNuevo
            ? "Usuario «{$usuario->username}» creado correctamente."
            : "Usuario «{$usuario->username}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modalDuplicadosAbierto = false;
        $this->bypassDuplicados = false;
        $this->duplicadosEncontrados = [];
        $this->form->reset();
        $this->usernameTocadoManual = false;
        $this->mostrarPassword = false;
        $this->modoSoloLectura = false;
        $this->passwordRenderKey++;
        $this->resetErrorBag();
    }

    /* ─────────────────── Duplicados (no bloqueantes) ───────────────────── */

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
        $this->modalDuplicadosAbierto = false;
        $this->duplicadosEncontrados = [];
        $this->modalAbierto = false;
        $this->form->reset();
        $this->abrirEditar($id);
    }

    /* ───────────────── Autosugerencia username ─────────────────────────── */

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
        if ($this->form->id !== null) {
            return;
        }

        if ($this->usernameTocadoManual) {
            return;
        }

        $this->form->username = UserForm::sugerirUsername($this->form->nombre, $this->form->apellidos);
    }

    public function generarPasswordSegura(): void
    {
        $password = Str::password(14, true, true, false, false);

        $this->form->password = $password;
        $this->mostrarPassword = true;
        $this->passwordRenderKey++;
        $this->resetValidation(['form.password']);
    }

    public function toggleMostrarPassword(): void
    {
        $this->mostrarPassword = ! $this->mostrarPassword;
        $this->passwordRenderKey++;
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
     * @return Collection<int, Cliente>
     */
    #[Computed]
    public function empresasDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * Roles que el usuario actual puede asignar (según jerarquía + permisos).
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

    #[Computed]
    public function accesoRolSeleccionado(): string
    {
        /** @var Role|null $rol */
        $rol = $this->rolesDisponibles()->firstWhere('name', $this->form->rol);

        return $rol?->acceso ?? '—';
    }

    private function primerRolDisponible(): string
    {
        $rol = $this->rolesDisponibles()->first();

        return $rol instanceof Role ? $rol->name : 'trabajador';
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

        $query->with(['roles:id,name,nivel,acceso', 'cliente:id,nombre']);

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

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.usuarios.index', [
            'usuarios' => $query->paginate($this->porPagina)->onEachSide(2),
            'modoPapelera' => $modoPapelera,
        ]);
    }
}
