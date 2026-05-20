<?php

namespace App\Livewire\Proyectos\Grupos;

use App\Livewire\Forms\GrupoProyectoForm;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'proyectos_grupos'])]
#[Title('Grupo proyectos')]
class Index extends Component
{
    use WithPagination;

    public GrupoProyectoForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $modalAbierto = false;

    public bool $modoSoloLectura = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public ?int $proyectoAAsignar = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', TiposProyecto::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['nombre', 'descripcion', 'activo', 'created_at'];
        if (! \in_array($columna, $permitidas, true)) {
            return;
        }

        if ($this->ordenColumna === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenColumna = $columna;
            $this->ordenDireccion = 'asc';
        }
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroEstado = 'todos';
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

    public function abrirCrear(): void
    {
        Gate::authorize('create', TiposProyecto::class);

        $this->form->reset();
        $this->form->activo = true;
        $this->proyectoAAsignar = null;
        $this->modoSoloLectura = false;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        /** @var TiposProyecto $grupo */
        $grupo = TiposProyecto::withTrashed()->findOrFail($id);
        Gate::authorize('view', $grupo);

        $this->form->fromModel($grupo);
        $this->proyectoAAsignar = null;
        $this->modoSoloLectura = true;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var TiposProyecto $grupo */
        $grupo = TiposProyecto::withTrashed()->findOrFail($id);
        Gate::authorize('update', $grupo);

        $this->form->fromModel($grupo);
        $this->proyectoAAsignar = null;
        $this->modoSoloLectura = false;
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        if ($this->modoSoloLectura) {
            abort(403);
        }

        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', TiposProyecto::class);
        } else {
            /** @var TiposProyecto $existente */
            $existente = TiposProyecto::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $grupo = $this->form->save();

        $this->modalAbierto = false;
        $this->proyectoAAsignar = null;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Grupo «{$grupo->nombre}» creado correctamente."
            : "Grupo «{$grupo->nombre}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modoSoloLectura = false;
        $this->proyectoAAsignar = null;
        $this->form->reset();
        $this->resetErrorBag();
    }

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
        /** @var TiposProyecto $grupo */
        $grupo = TiposProyecto::findOrFail($id);
        Gate::authorize('delete', $grupo);

        // Al enviar a papelera, los proyectos quedan sin grupo para evitar referencias colgantes.
        Proyecto::query()->where('tipo_proyecto_id', $grupo->id)->update(['tipo_proyecto_id' => null]);

        $grupo->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Grupo «{$grupo->nombre}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var TiposProyecto $grupo */
        $grupo = TiposProyecto::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $grupo);

        $grupo->restore();

        session()->flash('status', "Grupo «{$grupo->nombre}» restaurado.");
    }

    public function agregarProyectoAGrupo(): void
    {
        if ($this->form->id === null || $this->proyectoAAsignar === null) {
            return;
        }

        /** @var TiposProyecto $grupo */
        $grupo = TiposProyecto::findOrFail($this->form->id);
        Gate::authorize('update', $grupo);

        Proyecto::query()
            ->where('id', $this->proyectoAAsignar)
            ->whereNull('tipo_proyecto_id')
            ->update(['tipo_proyecto_id' => $grupo->id]);

        $this->proyectoAAsignar = null;
        unset($this->proyectosDelGrupoActual, $this->proyectosSinGrupo);
    }

    public function quitarProyectoDeGrupo(int $proyectoId): void
    {
        if ($this->form->id === null) {
            return;
        }

        /** @var TiposProyecto $grupo */
        $grupo = TiposProyecto::findOrFail($this->form->id);
        Gate::authorize('update', $grupo);

        Proyecto::query()
            ->where('id', $proyectoId)
            ->where('tipo_proyecto_id', $grupo->id)
            ->update(['tipo_proyecto_id' => null]);

        unset($this->proyectosDelGrupoActual, $this->proyectosSinGrupo);
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroEstado !== 'todos') {
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
     * @return EloquentCollection<int, Proyecto>
     */
    #[Computed(persist: false)]
    public function proyectosDelGrupoActual(): EloquentCollection
    {
        if ($this->form->id === null) {
            return new EloquentCollection;
        }

        return Proyecto::query()
            ->where('tipo_proyecto_id', $this->form->id)
            ->with('cliente:id,nombre')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'cliente_id', 'estado', 'tipo_proyecto_id']);
    }

    /**
     * @return EloquentCollection<int, Proyecto>
     */
    #[Computed(persist: false)]
    public function proyectosSinGrupo(): EloquentCollection
    {
        return Proyecto::query()
            ->whereNull('tipo_proyecto_id')
            ->with('cliente:id,nombre')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'cliente_id', 'estado', 'tipo_proyecto_id']);
    }

    public function render(): View
    {
        $query = TiposProyecto::query()->withCount('proyectos');

        if ($this->filtroEstado === 'activos') {
            $query->where('activo', true)->whereNull('deleted_at');
        } elseif ($this->filtroEstado === 'desactivados') {
            $query->where('activo', false)->whereNull('deleted_at');
        } elseif ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.proyectos.grupos.index', [
            'grupos' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
