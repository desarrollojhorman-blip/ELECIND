<?php

namespace App\Livewire\Clientes;

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

#[Layout('components.layouts.web', ['active' => 'clientes'])]
#[Title('Clientes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    #[Url(as: 'provincia')]
    public string $filtroProvincia = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    public bool $panelFiltrosAbierto = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Cliente::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroProvincia(): void
    {
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado   = '';
        $this->filtroProvincia = '';
        $this->buscar          = '';
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
        $this->filtroEstado = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroProvincia(): void
    {
        $this->filtroProvincia = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['codigo_cliente', 'nombre', 'cif', 'poblacion', 'email', 'telefono', 'activo', 'created_at'];
        if (! \in_array($columna, $columnasPermitidas, true)) {
            return;
        }

        if ($this->ordenColumna === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenColumna  = $columna;
            $this->ordenDireccion = 'asc';
        }
    }

    public function confirmarEliminar(int $id): void
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::findOrFail($id);
        Gate::authorize('delete', $cliente);

        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::findOrFail($id);
        Gate::authorize('delete', $cliente);

        $cliente->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Cliente «{$cliente->nombre}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $cliente);

        $cliente->restore();

        session()->flash('status', "Cliente «{$cliente->nombre}» restaurado. Se le asignó el código {$cliente->codigo_cliente} (el anterior quedó libre).");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroEstado !== '') {
            $count++;
        }
        if ($this->filtroProvincia !== '') {
            $count++;
        }

        return $count;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    /** @return Collection<int, string> */
    #[Computed]
    public function provinciasDisponibles(): Collection
    {
        return Cliente::query()
            ->withTrashed()
            ->whereNotNull('provincia')
            ->where('provincia', '!=', '')
            ->distinct()
            ->orderBy('provincia')
            ->pluck('provincia');
    }

    public function render(): View
    {
        $query = Cliente::query();

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'activas') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivas') {
            $query->where('activo', false);
        }

        if ($this->filtroProvincia !== '') {
            $query->where('provincia', 'like', '%'.trim($this->filtroProvincia).'%');
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('codigo_cliente', 'like', $termino)
                    ->orWhere('nombre', 'like', $termino)
                    ->orWhere('nombre_comercial', 'like', $termino)
                    ->orWhere('cif', 'like', $termino)
                    ->orWhere('email', 'like', $termino)
                    ->orWhere('telefono', 'like', $termino)
                    ->orWhere('poblacion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.clientes.index', [
            'clientes' => $query->paginate(15),
        ]);
    }
}
