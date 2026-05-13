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
    public ?int $filtroEmpresaCliente = null;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    /** @var array<int, array{campo: string, valor: string, usuario_id: int, usuario_nombre: string, eliminado: bool}> */
    public array $duplicadosEncontrados = [];

    public bool $modalDuplicadosAbierto = false;

    public bool $bypassDuplicados = false;

    public bool $usernameTocadoManual = false;

    public bool $mostrarPassword = false;

    public int $passwordRenderKey = 0;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
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

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = 'activos';
        $this->filtroTipo = null;
        $this->filtroRol = null;
        $this->filtroEmpresaCliente = null;
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
        $this->filtroEmpresaCliente = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['username', 'nombre', 'email', 'tipo_usuario', 'created_at'];
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
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::findOrFail($id);
        Gate::authorize('delete', $usuario);

        $usuario->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Usuario «{$usuario->username}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var User $usuario */
        $usuario = User::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $usuario);

        $usuario->restore();

        session()->flash('status', "Usuario «{$usuario->username}» restaurado.");
    }

    /* ───────────────────────── Computeds y catálogos ───────────────────── */

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
        if ($this->filtroEmpresaCliente !== null) {
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

        $query = User::query()
            ->with(['roles:id,name,nivel', 'cliente:id,nombre']);

        // Jerarquía: oculta usuarios con algún rol de nivel mayor al propio.
        $query->whereDoesntHave('roles', function (Builder $q) use ($nivelActual): void {
            $q->where('nivel', '>', $nivelActual);
        });

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivos') {
            $query->where('activo', false);
        }

        if ($this->filtroTipo !== null) {
            $query->where('tipo_usuario', $this->filtroTipo);
        }

        if ($this->filtroRol !== null) {
            $rol = $this->filtroRol;
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $rol));
        }

        if ($this->filtroEmpresaCliente !== null) {
            $query->where('cliente_id', $this->filtroEmpresaCliente);
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
            'usuarios' => $query->paginate(15),
        ]);
    }
}
