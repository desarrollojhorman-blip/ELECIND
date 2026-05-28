<?php

namespace App\Livewire\Incidencias;

use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'incidencias'])]
#[Title('Incidencias')]
class Index extends Component
{
    use WithPagination;

    // ── Búsqueda y filtros ───────────────────────────────────────
    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'trabajador')]
    public ?int $filtroTrabajador = null;

    #[Url(as: 'tipo')]
    public string $filtroTipo = '';

    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    #[Url(as: 'prioridad')]
    public string $filtroPrioridad = '';

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;
    public int  $resetKey            = 0;
    public int  $filtrosVersion      = 0;

    // ── Modal editar / ver ────────────────────────────────────────
    public bool   $modalAbierto     = false;
    public bool   $soloLectura      = false;
    public ?int   $editingId        = null;
    public string $formTrabajador   = '';
    public string $formTipo         = '';
    public string $formPrioridad    = 'media';
    public string $formTitulo       = '';
    public string $formDescripcion  = '';
    public string $formEstado       = '';
    public string $formResolucion   = '';

    // ── Confirmación eliminar ────────────────────────────────────
    public ?int $confirmarEliminarId = null;

    // ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        abort_unless(Gate::allows('incidencias.ver_todas') || Gate::allows('incidencias.modificar'), 403);
    }

    // ── Hooks de actualización ───────────────────────────────────

    public function updatedBuscar(): void           { $this->resetPage(); }
    public function updatedPorPagina(): void        { $this->resetPage(); }
    public function updatedFiltroTrabajador(): void { $this->resetPage(); }
    public function updatedFiltroTipo(): void       { $this->resetPage(); }
    public function updatedFiltroEstado(): void     { $this->resetPage(); }
    public function updatedFiltroPrioridad(): void  { $this->resetPage(); }
    public function updatedFechaDesde(): void       { $this->resetPage(); }
    public function updatedFechaHasta(): void       { $this->resetPage(); }

    // ── Filtros ──────────────────────────────────────────────────

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroTrabajador = null;
        $this->filtroTipo       = '';
        $this->filtroEstado     = '';
        $this->filtroPrioridad  = '';
        $this->fechaDesde       = '';
        $this->fechaHasta       = '';
        $this->buscar           = '';
        $this->resetKey++;
        $this->filtrosVersion++;
        $this->resetPage();
    }

    public function limpiarBuscador(): void
    {
        $this->buscar = '';
        $this->resetPage();
        $this->resetKey++;
    }

    public function quitarFiltroTrabajador(): void { $this->filtroTrabajador = null; $this->resetPage(); $this->resetKey++; $this->filtrosVersion++; }
    public function quitarFiltroTipo(): void       { $this->filtroTipo = '';         $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroEstado(): void     { $this->filtroEstado = '';       $this->resetPage(); $this->resetKey++; }
    public function quitarFiltroPrioridad(): void  { $this->filtroPrioridad = '';    $this->resetPage(); $this->resetKey++; }
    public function quitarFechaDesde(): void       { $this->fechaDesde = '';         $this->resetPage(); $this->resetKey++; $this->filtrosVersion++; }
    public function quitarFechaHasta(): void       { $this->fechaHasta = '';         $this->resetPage(); $this->resetKey++; $this->filtrosVersion++; }

    // ── Ordenación ───────────────────────────────────────────────

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['id', 'titulo', 'tipo', 'prioridad', 'estado', 'created_at'];
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

    // ── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        return User::query()
            ->role('trabajador')
            ->withTrashed()
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos', 'numero_empleado']);
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return (int) ($this->filtroTrabajador !== null)
            + (int) ($this->filtroTipo !== '')
            + (int) ($this->filtroEstado !== '')
            + (int) ($this->filtroPrioridad !== '')
            + (int) ($this->fechaDesde !== '')
            + (int) ($this->fechaHasta !== '');
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados > 0 || trim($this->buscar) !== '';
    }

    // ── Modal ────────────────────────────────────────────────────

    public function abrirVer(int $id): void
    {
        $this->cargarIncidencia($id);
        $this->soloLectura  = true;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        abort_unless(Gate::allows('incidencias.modificar'), 403);
        $this->cargarIncidencia($id);
        $this->soloLectura  = false;
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    private function cargarIncidencia(int $id): void
    {
        $inc = Incidencia::findOrFail($id);

        $this->editingId       = $inc->id;
        $this->formTrabajador  = (string) $inc->trabajador_id;
        $this->formTipo        = $inc->tipo->value;
        $this->formPrioridad   = $inc->prioridad->value;
        $this->formTitulo      = $inc->titulo;
        $this->formDescripcion = $inc->descripcion ?? '';
        $this->formEstado      = $inc->estado->value;
        $this->formResolucion  = $inc->resolucion ?? '';
    }

    public function guardar(): void
    {
        abort_unless(Gate::allows('incidencias.modificar'), 403);

        $estadoValues = implode(',', array_column(EstadoIncidencia::cases(), 'value'));

        $data = $this->validate([
            'formEstado'     => ['required', "in:{$estadoValues}"],
            'formResolucion' => ['nullable', 'string', 'max:1000'],
        ], [
            'formEstado.required' => 'El estado es obligatorio.',
        ]);

        $inc        = Incidencia::findOrFail($this->editingId);
        $estadoEnum = EstadoIncidencia::from($data['formEstado']);
        $resuelta   = in_array($estadoEnum, [EstadoIncidencia::RESUELTA, EstadoIncidencia::CERRADA], true);

        $payload = [
            'estado'       => $data['formEstado'],
            'resolucion'   => $data['formResolucion'] ?: null,
            'resuelto_por' => $resuelta && ! $inc->resuelto_por ? Auth::id() : $inc->resuelto_por,
            'resuelto_at'  => $resuelta && ! $inc->resuelto_at  ? now()       : $inc->resuelto_at,
        ];

        $inc->update($payload);

        $this->cerrarModal();
        $this->dispatch('toast', tipo: 'success', mensaje: 'Incidencia actualizada.');
    }

    // ── Eliminar ─────────────────────────────────────────────────

    public function confirmarEliminar(int $id): void
    {
        abort_unless(Gate::allows('incidencias.modificar'), 403);
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        abort_unless(Gate::allows('incidencias.modificar'), 403);
        Incidencia::findOrFail($id)->delete();
        $this->confirmarEliminarId = null;
        $this->dispatch('toast', tipo: 'success', mensaje: 'Incidencia eliminada.');
    }

    // ── Render ───────────────────────────────────────────────────

    public function render(): View
    {
        $query = Incidencia::with(['trabajador', 'resolutor'])
            ->when($this->filtroTrabajador, fn ($q) => $q->where('trabajador_id', $this->filtroTrabajador))
            ->when($this->filtroTipo,       fn ($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroEstado,     fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroPrioridad,  fn ($q) => $q->where('prioridad', $this->filtroPrioridad))
            ->when($this->fechaDesde,       fn ($q) => $q->whereDate('created_at', '>=', $this->fechaDesde))
            ->when($this->fechaHasta,       fn ($q) => $q->whereDate('created_at', '<=', $this->fechaHasta));

        if ($this->buscar !== '') {
            $term = '%' . trim($this->buscar) . '%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('titulo', 'like', $term)
                  ->orWhere('descripcion', 'like', $term)
                  ->orWhere('resolucion', 'like', $term)
                  ->orWhereHas('trabajador', fn ($u) => $u->where('nombre', 'like', $term)
                      ->orWhere('apellidos', 'like', $term));
            });
        }

        $permitidas = ['id', 'titulo', 'tipo', 'prioridad', 'estado', 'created_at'];
        $columna    = in_array($this->ordenColumna, $permitidas, true) ? $this->ordenColumna : 'id';

        if ($columna === 'prioridad') {
            $query->orderByRaw("FIELD(prioridad, 'urgente','alta','media','baja')" . ($this->ordenDireccion === 'asc' ? '' : ' DESC'));
        } else {
            $query->orderBy($columna, $this->ordenDireccion);
        }

        return view('livewire.incidencias.index', [
            'incidencias' => $query->paginate($this->porPagina)->onEachSide(2),
            'tipos'       => TipoIncidencia::cases(),
            'estados'     => EstadoIncidencia::cases(),
            'prioridades' => PrioridadIncidencia::cases(),
        ]);
    }
}
