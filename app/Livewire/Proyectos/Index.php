<?php

namespace App\Livewire\Proyectos;

use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
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

#[Layout('components.layouts.web', ['active' => 'proyectos_lista'])]
#[Title('Proyectos')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: todos | activo | inactivo | cerrado | papelera */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'tipo')]
    public ?int $filtroTipo = null;

    #[Url(as: 'cliente')]
    public ?int $filtroCliente = null;

    #[Url(as: 'responsable')]
    public ?int $filtroResponsable = null;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

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
        $columnasPermitidas = ['nombre', 'codigo', 'estado', 'fecha_inicio', 'albaranes_count'];
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
        $q = Cliente::query()->where('activo', true)->orderBy('nombre');
        $ids = auth()->user()?->idsClientesGestionados();
        if ($ids !== null) {
            $q->whereIn('id', $ids);
        }
        return $q->get(['id', 'nombre']);
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
        $query = Proyecto::query()
            ->with(['cliente:id,nombre', 'tipoProyecto:id,nombre'])
            ->withCount('albaranes');

        $clientesScope = auth()->user()?->idsClientesGestionados();
        if ($clientesScope !== null) {
            $query->whereIn('cliente_id', $clientesScope);
        }

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif (\in_array($this->filtroEstado, ['activo', 'inactivo', 'cerrado'], true)) {
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
            'proyectos' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
