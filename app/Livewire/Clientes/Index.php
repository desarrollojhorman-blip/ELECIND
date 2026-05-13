<?php

namespace App\Livewire\Clientes;

use App\Livewire\Forms\EmpresasClienteForm;
use App\Models\EmpresasCliente;
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

    public EmpresasClienteForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    /**
     * Estados: todos | activas | inactivas | papelera
     */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'provincia')]
    public string $filtroProvincia = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    public ?int $confirmarEliminarId = null;

    /**
     * Contador que se incrementa cada vez que se limpian filtros o búsqueda.
     * Lo usamos como sufijo de wire:key en inputs/selects para forzar a
     * Livewire a re-crear esos elementos del DOM (workaround del morphing).
     */
    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', EmpresasCliente::class);
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
        $this->filtroEstado = 'todos';
        $this->filtroProvincia = '';
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

    public function quitarFiltroProvincia(): void
    {
        $this->filtroProvincia = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['nombre', 'cif', 'poblacion', 'email', 'activo', 'created_at'];
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
        Gate::authorize('create', EmpresasCliente::class);

        $this->form->reset();
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var EmpresasCliente $empresa */
        $empresa = EmpresasCliente::withTrashed()->findOrFail($id);

        Gate::authorize('update', $empresa);

        $this->form->fromModel($empresa);
        $this->resetErrorBag();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', EmpresasCliente::class);
        } else {
            /** @var EmpresasCliente $existente */
            $existente = EmpresasCliente::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $empresa = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Empresa «{$empresa->nombre}» creada correctamente."
            : "Empresa «{$empresa->nombre}» actualizada correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
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
        /** @var EmpresasCliente $empresa */
        $empresa = EmpresasCliente::findOrFail($id);
        Gate::authorize('delete', $empresa);

        $empresa->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Empresa «{$empresa->nombre}» enviada a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var EmpresasCliente $empresa */
        $empresa = EmpresasCliente::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $empresa);

        $empresa->restore();

        session()->flash('status', "Empresa «{$empresa->nombre}» restaurada.");
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        if ($this->filtroEstado !== 'todos') {
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

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function provinciasDisponibles(): Collection
    {
        return EmpresasCliente::query()
            ->withTrashed()
            ->whereNotNull('provincia')
            ->where('provincia', '!=', '')
            ->distinct()
            ->orderBy('provincia')
            ->pluck('provincia');
    }

    public function render(): View
    {
        $query = EmpresasCliente::query();

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'activas') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivas') {
            $query->where('activo', false);
        }

        if ($this->filtroProvincia !== '') {
            $query->where('provincia', $this->filtroProvincia);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('nombre_comercial', 'like', $termino)
                    ->orWhere('cif', 'like', $termino)
                    ->orWhere('email', 'like', $termino)
                    ->orWhere('poblacion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.clientes.index', [
            'empresas' => $query->paginate(15),
        ]);
    }
}
