<?php

namespace App\Livewire\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\Cliente;
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

#[Layout('components.layouts.web', ['active' => 'albaranes'])]
#[Title('Albaranes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    #[Url(as: 'cliente')]
    public ?int $filtroCliente = null;

    #[Url(as: 'tipo')]
    public string $filtroTipo = '';

    #[Url(as: 'desde')]
    public string $filtroDesde = '';

    #[Url(as: 'hasta')]
    public string $filtroHasta = '';

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
        Gate::authorize('viewAny', Albaran::class);
    }

    public function updatedBuscar(): void { $this->resetPage(); }
    public function updatedFiltroEstado(): void { $this->resetPage(); }
    public function updatedFiltroCliente(): void { $this->resetPage(); }
    public function updatedFiltroTipo(): void { $this->resetPage(); }
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
        $this->filtroCliente = null;
        $this->filtroTipo = '';
        $this->filtroDesde = '';
        $this->filtroHasta = '';
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

    public function quitarFiltroEstado(): void { $this->filtroEstado = ''; $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroCliente(): void { $this->filtroCliente = null; $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroTipo(): void { $this->filtroTipo = ''; $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroDesde(): void { $this->filtroDesde = ''; $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroHasta(): void { $this->filtroHasta = ''; $this->resetPage(); $this->resetKey++; }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['numero', 'fecha', 'estado', 'tipo_hora'];
        if (! in_array($columna, $permitidas, true)) {
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
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        /** @var Albaran $albaran */
        $albaran = Albaran::findOrFail($id);
        Gate::authorize('delete', $albaran);

        $albaran->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Albarán «{$albaran->numero}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var Albaran $albaran */
        $albaran = Albaran::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $albaran);

        $albaran->restore();

        session()->flash('status', "Albarán «{$albaran->numero}» restaurado.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return collect([$this->filtroEstado, $this->filtroCliente, $this->filtroTipo, $this->filtroDesde, $this->filtroHasta])
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->count();
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados > 0 || trim($this->buscar) !== '';
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        $q = Cliente::query()->orderBy('nombre');
        $ids = auth()->user()?->idsClientesGestionados();
        if ($ids !== null) {
            $q->whereIn('id', $ids);
        }
        return $q->get(['id', 'nombre']);
    }

    public function render(): View
    {
        $query = Albaran::query()
            ->with([
                'cliente:id,nombre',
                'proyecto:id,nombre,codigo',
                'concepto:id,nombre',
                'creador:id,nombre,apellidos',
            ]);

        $clientesScope = auth()->user()?->idsClientesGestionados();
        if ($clientesScope !== null) {
            $query->whereIn('cliente_id', $clientesScope);
        }

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado !== '') {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroCliente !== null) {
            $query->where('cliente_id', $this->filtroCliente);
        }

        if ($this->filtroTipo !== '') {
            $query->where('tipo_hora', $this->filtroTipo);
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
                $q->where('numero', 'like', $termino)
                    ->orWhereHas('cliente', fn (Builder $c) => $c->where('nombre', 'like', $termino))
                    ->orWhereHas('proyecto', fn (Builder $p) => $p->where('nombre', 'like', $termino));
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.albaranes.index', [
            'albaranes' => $query->paginate($this->porPagina)->onEachSide(2),
            'estados' => EstadoAlbaran::cases(),
            'tiposHora' => TipoHora::cases(),
        ]);
    }
}
