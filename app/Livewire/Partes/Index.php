<?php

namespace App\Livewire\Partes;

use App\Models\Cliente;
use App\Models\Parte;
use App\Models\Proyecto;
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

/**
 * Listado de partes con toolbar estilo Albaranes:
 *   - search-and-filter component (búsqueda + botón "Filtros" desplegable).
 *   - Filtros: operario, proyecto, cliente, estado, ¿tiene albarán?, rango fechas.
 *   - Paginación + orden por columnas.
 */
#[Layout('components.layouts.web', ['active' => 'partes'])]
#[Title('Partes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'operario')]
    public ?int $filtroOperario = null;

    #[Url(as: 'proyecto')]
    public ?int $filtroProyecto = null;

    #[Url(as: 'cliente')]
    public ?int $filtroCliente = null;

    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    #[Url(as: 'con_albaran')]
    public string $filtroConAlbaran = ''; // '', 'si', 'no'

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'fecha';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Parte::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroOperario(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroProyecto(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroCliente(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroConAlbaran(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
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

    public function limpiarBuscador(): void
    {
        $this->buscar = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroOperario = null;
        $this->filtroProyecto = null;
        $this->filtroCliente = null;
        $this->filtroEstado = '';
        $this->filtroConAlbaran = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroOperario(): void
    {
        $this->filtroOperario = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroProyecto(): void
    {
        $this->filtroProyecto = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroCliente(): void
    {
        $this->filtroCliente = null;
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroEstado(): void
    {
        $this->filtroEstado = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroConAlbaran(): void
    {
        $this->filtroConAlbaran = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroFechas(): void
    {
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['numero', 'fecha', 'creador_apellidos_snapshot', 'proyecto_nombre_snapshot', 'cliente_nombre_snapshot', 'estado'];
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

    public function confirmarEliminar(int $id): void
    {
        $parte = Parte::findOrFail($id);
        Gate::authorize('delete', $parte);
        $parte->delete();
        session()->flash('status', "Parte «{$parte->numero}» eliminado.");
    }

    /* ── Computeds ────────────────────────────────────────────── */

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroOperario !== null) {
            $count++;
        }
        if ($this->filtroProyecto !== null) {
            $count++;
        }
        if ($this->filtroCliente !== null) {
            $count++;
        }
        if ($this->filtroEstado !== '') {
            $count++;
        }
        if ($this->filtroConAlbaran !== '') {
            $count++;
        }
        if ($this->fechaDesde !== '' || $this->fechaHasta !== '') {
            $count++;
        }

        return $count;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function operariosDisponibles(): Collection
    {
        return User::query()
            ->whereNull('deleted_at')
            ->whereDoesntHave('roles', fn ($q) => $q->where('es_externo', true))
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        return Proyecto::query()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    public function render(): View
    {
        $query = Parte::query()
            ->with(['creador:id,nombre,apellidos', 'proyecto:id,codigo,nombre']);

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('numero', 'like', $termino)
                    ->orWhere('observaciones', 'like', $termino)
                    ->orWhere('creador_apellidos_snapshot', 'like', $termino)
                    ->orWhere('creador_nombre_snapshot', 'like', $termino)
                    ->orWhere('proyecto_codigo_snapshot', 'like', $termino)
                    ->orWhere('proyecto_nombre_snapshot', 'like', $termino)
                    ->orWhere('cliente_nombre_snapshot', 'like', $termino);
            });
        }

        if ($this->filtroOperario) {
            $query->where('creado_por', $this->filtroOperario);
        }
        if ($this->filtroProyecto) {
            $query->where('proyecto_id', $this->filtroProyecto);
        }
        if ($this->filtroCliente) {
            $query->where('cliente_id', $this->filtroCliente);
        }
        if ($this->filtroEstado !== '') {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroConAlbaran === 'si') {
            $query->whereNotNull('albaran_id');
        } elseif ($this->filtroConAlbaran === 'no') {
            $query->whereNull('albaran_id');
        }
        if ($this->fechaDesde !== '') {
            $query->whereDate('fecha', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta !== '') {
            $query->whereDate('fecha', '<=', $this->fechaHasta);
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);
        if ($this->ordenColumna !== 'fecha') {
            $query->orderBy('fecha', 'desc');
        }

        return view('livewire.partes.index', [
            'partes' => $query->paginate($this->porPagina)->onEachSide(2),
            'totalPartes' => Parte::count(),
        ]);
    }
}
