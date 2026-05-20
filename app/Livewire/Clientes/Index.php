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

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    /**
     * Modo "Papelera" → muestra SOLO los clientes eliminados (soft-deleted).
     * Solo visible/aplicable para superadmin. Para el resto se ignora.
     */
    #[Url(as: 'papelera')]
    public bool $verPapelera = false;

    public bool $panelFiltrosAbierto = false;

    public ?int $confirmarEliminarId = null;

    /**
     * Si Gate::inspect('delete', $cliente) deniega por dependencias, el mensaje
     * llega aquí y se muestra el modal informativo (un solo botón "Entendido").
     */
    public ?string $bloqueadoEliminarMensaje = null;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Cliente::class);

        // Defensa: si alguien manipula la URL ?papelera=1 sin permiso,
        // lo ignoramos. (También se valida en render() pero mejor cortar antes.)
        if ($this->verPapelera && ! $this->puedeVerPapelera) {
            $this->verPapelera = false;
        }
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

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function updatedVerPapelera(): void
    {
        if ($this->verPapelera && ! $this->puedeVerPapelera) {
            $this->verPapelera = false;

            return;
        }
        $this->resetPage();
    }

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = '';
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
            $this->ordenColumna = $columna;
            $this->ordenDireccion = 'asc';
        }
    }

    /**
     * Comprueba si el cliente puede eliminarse (permiso + sin dependencias).
     * Si no puede, abre el modal informativo con el motivo.
     * Si puede, abre el modal de confirmación habitual.
     */
    public function confirmarEliminar(int $id): void
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::withTrashed()->findOrFail($id);

        $response = Gate::inspect('delete', $cliente);

        if (! $response->allowed()) {
            $this->bloqueadoEliminarMensaje = $response->message()
                ?: 'No tienes permiso para eliminar este cliente.';

            return;
        }

        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function cerrarBloqueo(): void
    {
        $this->bloqueadoEliminarMensaje = null;
    }

    public function eliminar(int $id): void
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::findOrFail($id);
        // Defensa server-side: si llegamos aquí saltándose la UI y hay deps,
        // Gate::authorize lanza AuthorizationException con el mensaje del Policy.
        Gate::authorize('delete', $cliente);

        $cliente->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Cliente «{$cliente->nombre}» eliminado correctamente.");
    }

    public function restaurar(int $id): void
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $cliente);

        $cliente->restore();

        session()->flash('status', "Cliente «{$cliente->nombre}» restaurado. Se le asignó el código {$cliente->codigo_cliente} (el anterior quedó libre).");
    }

    /* ── Exportar (filtros + orden actuales del listado) ─────────── */

    /**
     * Parámetros de URL para los exports: filtros y orden vigentes EN ESTE
     * INSTANTE. Construirlos en PHP (no en el blade) elimina cualquier
     * dependencia del estado del DOM al pulsar el botón.
     *
     * @return array<string, string>
     */
    private function paramsExport(): array
    {
        return [
            'q' => $this->buscar,
            'estado' => $this->filtroEstado,
            'provincia' => $this->filtroProvincia,
            'orden' => $this->ordenColumna,
            'dir' => $this->ordenDireccion,
        ];
    }

    public function exportarExcel(): void
    {
        Gate::authorize('clientes.exportar');

        $this->dispatch(
            'descargar',
            url: route('clientes.exportar.excel', $this->paramsExport())
        );
    }

    public function exportarPdf(string $orientacion): void
    {
        Gate::authorize('clientes.exportar');

        if (! in_array($orientacion, ['vertical', 'horizontal'], true)) {
            abort(404);
        }

        $this->dispatch(
            'descargar',
            url: route('clientes.exportar.pdf', array_merge(['orientacion' => $orientacion], $this->paramsExport()))
        );
    }

    /* ── Computeds ─────────────────────────────────────────────── */

    /**
     * Total de clientes "vivos" (activos + inactivos, EXCLUYE papelera).
     * Es el número que se muestra junto al título y NO cambia entre modos
     * (el modo papelera solo afecta a la tabla; el contador se mantiene).
     */
    #[Computed]
    public function totalClientes(): int
    {
        return Cliente::count();
    }

    /**
     * Total de clientes en papelera. Solo se usa para la etiqueta del
     * checkbox/banner de modo papelera (visible solo a superadmin).
     */
    #[Computed]
    public function totalPapelera(): int
    {
        return Cliente::onlyTrashed()->count();
    }

    /**
     * Puede ver/gestionar la papelera de clientes (ver eliminados + restaurar).
     * Protegido por el permiso `clientes.gestionar_papelera`; por defecto solo
     * el superadmin lo tiene, pero técnicamente se puede dar a cualquier rol
     * desde la pantalla de Roles sin tocar código.
     */
    #[Computed]
    public function puedeVerPapelera(): bool
    {
        return auth()->user()?->can('clientes.gestionar_papelera') ?? false;
    }

    #[Computed]
    public function subtituloListado(): string
    {
        $total = $this->totalClientes;

        return $total === 1
            ? '1 cliente · activo o inactivo'
            : "{$total} clientes · activos e inactivos";
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        $count = 0;
        // En modo papelera ignoramos el filtro Estado.
        if (! $this->verPapelera && $this->filtroEstado !== '') {
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
        // Modo papelera: SOLO trashed, ignora filtros de Estado (los demás siguen).
        $modoPapelera = $this->verPapelera && $this->puedeVerPapelera;

        $query = $modoPapelera
            ? Cliente::onlyTrashed()
            : Cliente::query();

        if (! $modoPapelera) {
            // Estado: activas / inactivas / (vacío = ambas). La opción "papelera"
            // de versiones anteriores ya no aplica desde la UI; si llega por URL
            // antigua, se ignora (no se filtra nada).
            if ($this->filtroEstado === 'activas') {
                $query->where('activo', true);
            } elseif ($this->filtroEstado === 'inactivas') {
                $query->where('activo', false);
            }
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
            'clientes' => $query->paginate($this->porPagina)->onEachSide(2),
            'modoPapelera' => $modoPapelera,
        ]);
    }
}
