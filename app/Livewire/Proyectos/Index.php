<?php

namespace App\Livewire\Proyectos;

use App\Livewire\Forms\ProyectoForm;
use App\Livewire\Forms\TipoProyectoQuickForm;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'proyectos_lista'])]
#[Title('Proyectos')]
class Index extends Component
{
    use WithPagination;

    public ProyectoForm $form;

    public TipoProyectoQuickForm $tipoForm;

    /** Selector de grupo en UI: '' | id | '__otro__' */
    public string $selectorGrupo = '';

    public ?string $nuevoGrupoNombre = null;

    public ?int $trabajadorAAgregar = null;

    public ?int $responsableAAgregar = null;

    public int $trabajadorSelectKey = 0;

    public int $responsableSelectKey = 0;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: todos | activo | cerrado | archivado | papelera */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'tipo')]
    public ?int $filtroTipo = null;

    #[Url(as: 'cliente')]
    public ?int $filtroCliente = null;

    #[Url(as: 'responsable')]
    public ?int $filtroResponsable = null;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public bool $modoSoloLectura = false;

    public bool $modalTipoAbierto = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Proyecto::class);
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

    public function updatedFiltroCliente(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroResponsable(): void
    {
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = 'todos';
        $this->filtroTipo = null;
        $this->filtroCliente = null;
        $this->filtroResponsable = null;
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
        $this->filtroEstado = 'todos';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroTipo(): void
    {
        $this->filtroTipo = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroCliente(): void
    {
        $this->filtroCliente = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroResponsable(): void
    {
        $this->filtroResponsable = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['nombre', 'codigo', 'estado', 'fecha_inicio', 'created_at'];
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

    public function abrirCrear(): void
    {
        Gate::authorize('create', Proyecto::class);

        $this->form->reset();
        $this->modoSoloLectura = false;
        $this->form->estado = 'activo';
        $this->selectorGrupo = '';
        $this->nuevoGrupoNombre = null;
        $this->trabajadorAAgregar = null;
        $this->responsableAAgregar = null;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::withTrashed()->findOrFail($id);

        Gate::authorize('update', $proyecto);

        $this->form->fromModel($proyecto);
        $this->modoSoloLectura = false;
        $this->selectorGrupo = $this->form->tipo_proyecto_id !== null ? (string) $this->form->tipo_proyecto_id : '';
        $this->nuevoGrupoNombre = null;
        $this->trabajadorAAgregar = null;
        $this->responsableAAgregar = null;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::withTrashed()->findOrFail($id);

        Gate::authorize('view', $proyecto);

        $this->form->fromModel($proyecto);
        $this->modoSoloLectura = true;
        $this->selectorGrupo = $this->form->tipo_proyecto_id !== null ? (string) $this->form->tipo_proyecto_id : '';
        $this->nuevoGrupoNombre = null;
        $this->trabajadorAAgregar = null;
        $this->responsableAAgregar = null;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        if ($this->modoSoloLectura) {
            return;
        }

        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', Proyecto::class);
        } else {
            /** @var Proyecto $existente */
            $existente = Proyecto::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $this->resolverGrupoSeleccionado();

        $proyecto = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();
        $this->selectorGrupo = '';
        $this->nuevoGrupoNombre = null;
        $this->trabajadorAAgregar = null;
        $this->responsableAAgregar = null;

        session()->flash('status', $esNuevo
            ? "Proyecto «{$proyecto->nombre}» creado correctamente."
            : "Proyecto «{$proyecto->nombre}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modoSoloLectura = false;
        $this->form->reset();
        $this->selectorGrupo = '';
        $this->nuevoGrupoNombre = null;
        $this->trabajadorAAgregar = null;
        $this->responsableAAgregar = null;
        $this->resetErrorBag();
    }

    public function agregarTrabajador(): void
    {
        if ($this->form->id === null) {
            return;
        }

        $this->validate([
            'trabajadorAAgregar' => ['required', 'integer', Rule::exists('users', 'id')],
        ], [], [
            'trabajadorAAgregar' => 'trabajador',
        ]);

        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::findOrFail($this->form->id);
        Gate::authorize('update', $proyecto);

        $yaExiste = $proyecto->usuarios()
            ->where('users.id', $this->trabajadorAAgregar)
            ->exists();

        if ($yaExiste) {
            $this->addError('trabajadorAAgregar', 'Este usuario ya está asignado al proyecto.');

            return;
        }

        $proyecto->usuarios()->attach($this->trabajadorAAgregar, ['rol_en_proyecto' => 'trabajador']);
        $this->trabajadorAAgregar = null;
        $this->trabajadorSelectKey++;
    }

    public function quitarTrabajador(int $userId): void
    {
        if ($this->form->id === null) {
            return;
        }

        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::findOrFail($this->form->id);
        Gate::authorize('update', $proyecto);

        $proyecto->usuarios()
            ->wherePivot('rol_en_proyecto', 'trabajador')
            ->detach($userId);
    }

    public function agregarResponsableProyecto(): void
    {
        if ($this->form->id === null) {
            return;
        }

        $this->validate([
            'responsableAAgregar' => ['required', 'integer', Rule::exists('users', 'id')],
        ], [], [
            'responsableAAgregar' => 'responsable',
        ]);

        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::findOrFail($this->form->id);
        Gate::authorize('update', $proyecto);

        $yaExiste = $proyecto->usuarios()
            ->where('users.id', $this->responsableAAgregar)
            ->exists();

        if ($yaExiste) {
            $this->addError('responsableAAgregar', 'Este usuario ya está asignado al proyecto.');

            return;
        }

        $proyecto->usuarios()->attach($this->responsableAAgregar, ['rol_en_proyecto' => 'responsable']);
        $this->responsableAAgregar = null;
        $this->responsableSelectKey++;
    }

    public function quitarResponsableProyecto(int $userId): void
    {
        if ($this->form->id === null) {
            return;
        }

        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::findOrFail($this->form->id);
        Gate::authorize('update', $proyecto);

        $proyecto->usuarios()
            ->wherePivot('rol_en_proyecto', 'responsable')
            ->detach($userId);
    }

    /* ───────────── Sub-modal: crear tipo de proyecto al vuelo ───────────── */

    public function abrirModalTipo(): void
    {
        Gate::authorize('create', TiposProyecto::class);

        $this->tipoForm->reset();
        $this->resetErrorBag('tipoForm.*');
        $this->modalTipoAbierto = true;
    }

    public function cerrarModalTipo(): void
    {
        $this->modalTipoAbierto = false;
        $this->tipoForm->reset();
        $this->resetErrorBag('tipoForm.*');
    }

    public function guardarTipo(): void
    {
        Gate::authorize('create', TiposProyecto::class);

        $tipo = $this->tipoForm->save();

        // Auto-seleccionar el nuevo tipo en el form principal
        $this->form->tipo_proyecto_id = (int) $tipo->getKey();
        $this->selectorGrupo = (string) $tipo->getKey();
        $this->nuevoGrupoNombre = null;

        $this->modalTipoAbierto = false;
        $this->tipoForm->reset();

        session()->flash('status', "Tipo «{$tipo->nombre}» creado y seleccionado.");
    }

    /* ───────────────────── Eliminación / restauración ───────────────────── */

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
        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::findOrFail($id);
        Gate::authorize('delete', $proyecto);

        $proyecto->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Proyecto «{$proyecto->nombre}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var Proyecto $proyecto */
        $proyecto = Proyecto::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $proyecto);

        $proyecto->restore();

        session()->flash('status', "Proyecto «{$proyecto->nombre}» restaurado.");
    }

    /* ───────────────────────── Computeds y catálogos ────────────────────── */

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroEstado !== 'todos') {
            $count++;
        }
        if ($this->filtroTipo !== null) {
            $count++;
        }
        if ($this->filtroCliente !== null) {
            $count++;
        }
        if ($this->filtroResponsable !== null) {
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
     * @return Collection<int, TiposProyecto>
     */
    #[Computed]
    public function tiposDisponibles(): Collection
    {
        return TiposProyecto::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * @return Collection<int, Cliente>
     */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesDisponibles(): Collection
    {
        return User::query()
            ->where('tipo_usuario', 'interno')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /* ───────────────────────────── Render ───────────────────────────────── */

    public function render(): View
    {
        $query = Proyecto::query()->with(['cliente:id,nombre', 'tipoProyecto:id,nombre', 'responsablePrincipal:id,nombre,apellidos']);

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif (\in_array($this->filtroEstado, ['activo', 'cerrado', 'archivado'], true)) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroTipo !== null) {
            $query->where('tipo_proyecto_id', $this->filtroTipo);
        }

        if ($this->filtroCliente !== null) {
            $query->where('cliente_id', $this->filtroCliente);
        }

        if ($this->filtroResponsable !== null) {
            $query->where('responsable_principal_id', $this->filtroResponsable);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('codigo', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.proyectos.index', [
            'proyectos' => $query->paginate(15),
        ]);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        if ($this->form->id === null) {
            return collect();
        }

        $asignados = $this->trabajadoresProyecto->pluck('id')->all();

        return User::query()
            ->where('tipo_usuario', 'interno')
            ->where('activo', true)
            ->whereNotIn('id', $asignados)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesProyectoDisponibles(): Collection
    {
        if ($this->form->id === null) {
            return collect();
        }

        $asignados = $this->responsablesProyecto->pluck('id')->all();

        return User::query()
            ->where('tipo_usuario', 'externo')
            ->where('activo', true)
            ->whereNotIn('id', $asignados)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function trabajadoresProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('trabajador');
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('responsable');
    }

    private function resolverGrupoSeleccionado(): void
    {
        if ($this->selectorGrupo === '__otro__') {
            $this->validate([
                'nuevoGrupoNombre' => ['required', 'string', 'max:255'],
            ], [], [
                'nuevoGrupoNombre' => 'nuevo grupo',
            ]);

            $nombre = trim((string) $this->nuevoGrupoNombre);

            $grupoExistente = TiposProyecto::withTrashed()
                ->where('nombre', $nombre)
                ->first();

            if ($grupoExistente !== null) {
                if ($grupoExistente->trashed()) {
                    $grupoExistente->restore();
                }

                if (! $grupoExistente->activo) {
                    $grupoExistente->activo = true;
                    $grupoExistente->save();
                }

                $this->form->tipo_proyecto_id = (int) $grupoExistente->getKey();
                $this->selectorGrupo = (string) $grupoExistente->getKey();

                return;
            }

            $grupo = TiposProyecto::create([
                'nombre' => $nombre,
                'descripcion' => null,
                'activo' => true,
            ]);

            $this->form->tipo_proyecto_id = (int) $grupo->getKey();
            $this->selectorGrupo = (string) $grupo->getKey();

            return;
        }

        $this->nuevoGrupoNombre = null;

        if ($this->selectorGrupo === '') {
            $this->form->tipo_proyecto_id = null;

            return;
        }

        $this->form->tipo_proyecto_id = (int) $this->selectorGrupo;
    }

    /**
     * @return Collection<int, User>
     */
    private function usuariosProyectoPorRol(string $rol): Collection
    {
        if ($this->form->id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['usuarios' => function ($q) use ($rol): void {
                $q->wherePivot('rol_en_proyecto', $rol)
                    ->orderBy('nombre')
                    ->orderBy('apellidos');
            }])
            ->find($this->form->id);

        if (! $proyecto instanceof Proyecto) {
            return collect();
        }

        /** @var EloquentCollection<int, User> $usuarios */
        $usuarios = $proyecto->usuarios;

        return $usuarios->toBase();
    }
}
