<?php

namespace App\Livewire\Roles;

use App\Livewire\Forms\RoleForm;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'roles'])]
#[Title('Roles y permisos')]
class Index extends Component
{
    use WithPagination;

    public RoleForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** todos | sistema | personalizados */
    #[Url(as: 'tipo')]
    public string $filtroTipo = 'todos';

    #[Url(as: 'ambito')]
    public ?string $filtroAmbito = null;

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    /** Confirmación al cambiar de ámbito en edición con permisos previos. */
    public bool $modalCambioAmbitoAbierto = false;

    public string $ambitoAnterior = '';

    public string $ambitoNuevoPendiente = '';

    public int $cantidadPermisosAfectados = 0;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Role::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroAmbito(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroTipo = 'todos';
        $this->filtroAmbito = null;
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

    public function quitarFiltroTipo(): void
    {
        $this->filtroTipo = 'todos';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroAmbito(): void
    {
        $this->filtroAmbito = null;
        $this->resetPage();
        $this->resetKey++;
    }

    /* ───────────────────────── Modal alta / edición ────────────────────── */

    public function abrirCrear(): void
    {
        Gate::authorize('create', Role::class);

        $this->form->reset();
        $this->form->nivel = 10;
        $this->form->acceso = 'web';
        $this->ambitoAnterior = 'web';
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var Role $rol */
        $rol = Role::findOrFail($id);

        Gate::authorize('update', $rol);

        $this->form->fromModel($rol);
        $this->ambitoAnterior = $rol->acceso;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', Role::class);
        } else {
            /** @var Role $existente */
            $existente = Role::findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        if (! Gate::allows('puedeAsignarAmbito', [Role::class, $this->form->acceso])) {
            $this->addError('form.acceso', 'No tienes permiso para asignar este ámbito.');

            return;
        }

        // El nivel no puede superar el del creador.
        /** @var User $actual */
        $actual = auth()->user();
        if ($this->form->nivel > $actual->nivelMaximo()) {
            $this->addError('form.nivel', 'No puedes asignar un nivel superior al tuyo.');

            return;
        }

        $rol = $this->form->save($actual);

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Rol «{$rol->name}» creado correctamente."
            : "Rol «{$rol->name}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->form->reset();
        $this->resetErrorBag();
    }

    /* ───────────── Cambio de ámbito con confirmación (reset permisos) ──── */

    public function updatedFormAcceso(string $nuevoAmbito): void
    {
        // En alta no hay permisos previos: solo guardamos el ámbito.
        if ($this->form->id === null) {
            $this->ambitoAnterior = $nuevoAmbito;

            return;
        }

        if ($nuevoAmbito === $this->ambitoAnterior) {
            return;
        }

        if (count($this->form->permisos) === 0) {
            // No hay permisos previos: cambio limpio sin confirmar.
            $this->ambitoAnterior = $nuevoAmbito;

            return;
        }

        // Hay permisos asignados: pedimos confirmación.
        $this->cantidadPermisosAfectados = count($this->form->permisos);
        $this->ambitoNuevoPendiente = $nuevoAmbito;
        $this->modalCambioAmbitoAbierto = true;
    }

    public function confirmarCambioAmbito(): void
    {
        $this->form->permisos = [];
        $this->ambitoAnterior = $this->ambitoNuevoPendiente;
        $this->modalCambioAmbitoAbierto = false;
        $this->ambitoNuevoPendiente = '';
        $this->cantidadPermisosAfectados = 0;

        session()->flash('status', 'Se eliminaron los permisos anteriores. Asigna los nuevos para este ámbito.');
    }

    public function cancelarCambioAmbito(): void
    {
        // Revertimos el ámbito al anterior.
        $this->form->acceso = $this->ambitoAnterior;
        $this->modalCambioAmbitoAbierto = false;
        $this->ambitoNuevoPendiente = '';
        $this->cantidadPermisosAfectados = 0;
    }

    /* ───────────────────────── Eliminar ────────────────────────────────── */

    public function confirmarEliminar(int $id): void
    {
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    /* ───────────── Toggle masivo de permisos por categoría ─────────────── */

    /**
     * Marca/desmarca todos los permisos de una categoría.
     * Si todos están ya marcados → desmarca todos.
     * En cualquier otro caso → marca todos los visibles para el creador.
     */
    public function toggleCategoria(string $categoria): void
    {
        $idsCategoria = collect($this->permisosAgrupados[$categoria] ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($idsCategoria) === 0) {
            return;
        }

        $yaSeleccionados = array_intersect($this->form->permisos, $idsCategoria);

        if (count($yaSeleccionados) === count($idsCategoria)) {
            // Todos marcados → desmarcamos todos.
            $this->form->permisos = array_values(array_diff($this->form->permisos, $idsCategoria));
        } else {
            // Marcamos todos.
            $this->form->permisos = array_values(array_unique([...$this->form->permisos, ...$idsCategoria]));
        }
    }

    /**
     * Estado del checkbox cabecera: 'all' | 'some' | 'none'.
     */
    public function estadoCategoria(string $categoria): string
    {
        $idsCategoria = collect($this->permisosAgrupados[$categoria] ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($idsCategoria) === 0) {
            return 'none';
        }

        $seleccionados = count(array_intersect($this->form->permisos, $idsCategoria));

        if ($seleccionados === 0) {
            return 'none';
        }

        return $seleccionados === count($idsCategoria) ? 'all' : 'some';
    }

    public function eliminar(int $id): void
    {
        /** @var Role $rol */
        $rol = Role::findOrFail($id);
        Gate::authorize('delete', $rol);

        // Un rol asignado a usuarios no se puede eliminar (perderían su rol).
        if ($rol->users()->exists()) {
            session()->flash('error', "El rol «{$rol->nombreVisible()}» está asignado a usuarios y no se puede eliminar. Reasigna primero esos usuarios.");
            $this->confirmarEliminarId = null;

            return;
        }

        $nombre = $rol->nombreVisible();
        $rol->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Rol «{$nombre}» eliminado.");
    }

    /* ───────────────────────── Computeds ───────────────────────────────── */

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroTipo !== 'todos') {
            $count++;
        }
        if ($this->filtroAmbito !== null) {
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
     * Permisos asignables al rol actual (filtrados por ámbito + delegación), agrupados por categoría.
     * Dentro de cada categoría, los permisos se ordenan por ámbito: web → movil → ambos.
     *
     * @return array<string, array<int, Permission>>
     */
    #[Computed]
    public function permisosAgrupados(): array
    {
        /** @var User $actual */
        $actual = auth()->user();

        $permisos = $this->form->permisosAsignablesPor($actual, $this->form->acceso);

        $ordenAmbito = ['web' => 1, 'movil' => 2, 'ambos' => 3];

        $agrupados = [];
        foreach ($permisos as $permiso) {
            $categoria = $permiso->categoria ?? 'otros';
            $agrupados[$categoria][] = $permiso;
        }

        foreach ($agrupados as $cat => $items) {
            usort($agrupados[$cat], function (Permission $a, Permission $b) use ($ordenAmbito): int {
                $cmp = ($ordenAmbito[$a->ambito] ?? 99) <=> ($ordenAmbito[$b->ambito] ?? 99);

                return $cmp !== 0 ? $cmp : strcmp($a->name, $b->name);
            });
        }

        return $agrupados;
    }

    /**
     * Ámbitos que el usuario actual puede asignar.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function ambitosAsignables(): array
    {
        /** @var User $actual */
        $actual = auth()->user();

        return $actual->hasRole('superadmin')
            ? ['web', 'movil', 'ambos']
            : ['web', 'movil'];
    }

    /* ───────────────────────── Render ──────────────────────────────────── */

    public function render(): View
    {
        $query = Role::query()->withCount(['permissions', 'users']);

        if ($this->filtroTipo === 'sistema') {
            $query->where('es_sistema', true);
        } elseif ($this->filtroTipo === 'personalizados') {
            $query->where('es_sistema', false);
        }

        if ($this->filtroAmbito !== null) {
            $query->where('acceso', $this->filtroAmbito);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('name', 'like', $termino);
            });
        }

        // Scoping por nivel: no ves roles de nivel superior al tuyo.
        /** @var User $actual */
        $actual = auth()->user();
        $query->where('nivel', '<=', $actual->nivelMaximo());

        $query->orderByDesc('nivel')->orderBy('name');

        return view('livewire.roles.index', [
            'roles' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
