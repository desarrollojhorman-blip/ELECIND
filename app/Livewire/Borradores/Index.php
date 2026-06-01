<?php

namespace App\Livewire\Borradores;

use App\Models\Borrador;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'borradores'])]
#[Title('Borradores')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    #[Url(as: 'desde')]
    public string $filtroDesde = '';

    #[Url(as: 'hasta')]
    public string $filtroHasta = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'fecha';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Borrador::class);
    }

    public function updatedBuscar(): void { $this->resetPage(); }
    public function updatedFiltroEstado(): void { $this->resetPage(); }
    public function updatedFiltroDesde(): void { $this->resetPage(); }
    public function updatedFiltroHasta(): void { $this->resetPage(); }
    public function updatedPorPagina(): void { $this->resetPage(); }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = '';
        $this->filtroDesde  = '';
        $this->filtroHasta  = '';
        $this->buscar       = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroEstado(): void { $this->filtroEstado = ''; $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroDesde(): void { $this->filtroDesde = ''; $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroHasta(): void { $this->filtroHasta = ''; $this->resetPage(); $this->resetKey++; }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['numero_borrador', 'fecha', 'estado'];
        if (! in_array($columna, $permitidas, true)) {
            return;
        }

        if ($this->ordenColumna === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenColumna   = $columna;
            $this->ordenDireccion = 'asc';
        }
    }

    public function confirmarEliminar(int $id): void
    {
        $borrador = Borrador::findOrFail($id);
        Gate::authorize('delete', $borrador);
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        /** @var Borrador $borrador */
        $borrador = Borrador::findOrFail($id);
        Gate::authorize('delete', $borrador);

        $borrador->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Borrador «{$borrador->numero_borrador}» eliminado.");
    }

    public function restaurar(int $id): void
    {
        /** @var Borrador $borrador */
        $borrador = Borrador::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $borrador);

        $borrador->restore();
        session()->flash('status', "Borrador «{$borrador->numero_borrador}» restaurado.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return collect([$this->filtroEstado, $this->filtroDesde, $this->filtroHasta])
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->count();
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados > 0 || trim($this->buscar) !== '';
    }

    public function render(): View
    {
        $query = Borrador::query()
            ->with(['creador:id,nombre,apellidos']);

        $clientesScope = auth()->user()?->idsClientesGestionados();
        if ($clientesScope !== null) {
            $query->whereIn('cliente_id', $clientesScope);
        }

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado !== '') {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroDesde !== '') {
            $query->whereDate('fecha', '>=', $this->filtroDesde);
        }

        if ($this->filtroHasta !== '') {
            $query->whereDate('fecha', '<=', $this->filtroHasta);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('numero_borrador', 'like', $termino)
                    ->orWhere('proyecto_texto', 'like', $termino)
                    ->orWhere('cliente_texto', 'like', $termino)
                    ->orWhereHas('proyecto', fn (Builder $p) => $p->where('nombre', 'like', $termino))
                    ->orWhereHas('cliente', fn (Builder $c) => $c->where('nombre', 'like', $termino));
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.borradores.index', [
            'borradores' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
