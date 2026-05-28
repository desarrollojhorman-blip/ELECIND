<?php

namespace App\Livewire\Ausencias;

use App\Enums\EstadoAusencia;
use App\Enums\TipoAusencia;
use App\Models\Ausencia;
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

#[Layout('components.layouts.web', ['active' => 'ausencias'])]
#[Title('Ausencias')]
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

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    #[Url(as: 'papelera')]
    public bool $verPapelera = false;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;
    public int  $resetKey = 0;
    public int  $filtrosVersion = 0;

    // ── Modal crear / editar / ver ────────────────────────────────
    public bool   $modalAbierto     = false;
    public bool   $soloLectura      = false;
    public ?int   $editingId        = null;
    public string $formTrabajador   = '';
    public string $formTipo         = '';
    public string $formEstadoForm   = '';
    public string $formFechaInicio  = '';
    public string $formFechaFin     = '';
    public string $formMotivo       = '';
    public string $formObservaciones = '';

    // ── Confirmación eliminar ────────────────────────────────────
    public ?int $confirmarEliminarId = null;

    // ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas') || Gate::allows('ausencias.aprobar'), 403);
    }

    // ── Hooks de actualización ───────────────────────────────────

    public function updatedBuscar(): void          { $this->resetPage(); }
    public function updatedPorPagina(): void       { $this->resetPage(); }
    public function updatedFiltroTrabajador(): void { $this->resetPage(); }
    public function updatedFiltroTipo(): void       { $this->resetPage(); }
    public function updatedFiltroEstado(): void     { $this->resetPage(); }
    public function updatedFechaDesde(): void       { $this->resetPage(); }
    public function updatedFechaHasta(): void       { $this->resetPage(); }

    public function updatedVerPapelera(): void
    {
        if ($this->verPapelera && ! $this->puedeVerPapelera) {
            $this->verPapelera = false;
            return;
        }
        $this->resetPage();
    }

    // ── Filtros ──────────────────────────────────────────────────

    public function togglePanelFiltros(): void
    {
        $this->panelFiltrosAbierto = ! $this->panelFiltrosAbierto;
    }

    public function aplicarFiltros(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->buscar           = '';
        $this->filtroTrabajador = null;
        $this->filtroTipo       = '';
        $this->filtroEstado     = '';
        $this->fechaDesde       = '';
        $this->fechaHasta       = '';
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
    public function quitarFechaDesde(): void       { $this->fechaDesde = '';         $this->resetPage(); $this->resetKey++; $this->filtrosVersion++; }
    public function quitarFechaHasta(): void       { $this->fechaHasta = '';         $this->resetPage(); $this->resetKey++; $this->filtrosVersion++; }

    // ── Ordenación ───────────────────────────────────────────────

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['id', 'fecha_inicio', 'fecha_fin', 'estado', 'tipo'];
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
    public function puedeVerPapelera(): bool
    {
        return Gate::allows('ausencias.ver_todas');
    }

    #[Computed]
    public function totalPapelera(): int
    {
        return Ausencia::onlyTrashed()->count();
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        return (int) ($this->filtroTrabajador !== null)
            + (int) ($this->filtroTipo !== '')
            + (int) ($this->filtroEstado !== '')
            + (int) ($this->fechaDesde !== '')
            + (int) ($this->fechaHasta !== '');
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados > 0 || trim($this->buscar) !== '';
    }

    // ── Modal ────────────────────────────────────────────────────

    public function abrirCrear(): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas'), 403);

        $this->reset(['editingId', 'formTrabajador', 'formTipo', 'formEstadoForm',
            'formFechaInicio', 'formFechaFin', 'formMotivo', 'formObservaciones']);
        $this->formFechaInicio = now()->format('Y-m-d');
        $this->formFechaFin    = now()->format('Y-m-d');
        $this->soloLectura     = false;
        $this->modalAbierto    = true;
    }

    public function abrirVer(int $id): void
    {
        $this->cargarAusencia($id);
        $this->soloLectura  = true;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas'), 403);
        $this->cargarAusencia($id);
        $this->soloLectura  = false;
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    private function cargarAusencia(int $id): void
    {
        $ausencia = Ausencia::withTrashed()->findOrFail($id);

        $this->editingId         = $ausencia->id;
        $this->formTrabajador    = (string) $ausencia->trabajador_id;
        $this->formTipo          = $ausencia->tipo->value;
        $this->formEstadoForm    = $ausencia->estado->value;
        $this->formFechaInicio   = $ausencia->fecha_inicio->format('Y-m-d');
        $this->formFechaFin      = $ausencia->fecha_fin->format('Y-m-d');
        $this->formMotivo        = $ausencia->motivo ?? '';
        $this->formObservaciones = $ausencia->observaciones ?? '';
    }

    public function guardar(): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas'), 403);

        $tipoValues   = implode(',', array_column(TipoAusencia::cases(), 'value'));
        $estadoValues = implode(',', array_column(EstadoAusencia::cases(), 'value'));

        $rules = [
            'formTrabajador'    => ['required', 'exists:users,id'],
            'formTipo'          => ['required', "in:{$tipoValues}"],
            'formFechaInicio'   => ['required', 'date'],
            'formFechaFin'      => ['required', 'date', 'gte:formFechaInicio'],
            'formMotivo'        => ['nullable', 'string', 'max:500'],
            'formObservaciones' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->editingId) {
            $rules['formEstadoForm'] = ['required', "in:{$estadoValues}"];
        }

        $data = $this->validate($rules, [
            'formTrabajador.required'  => 'Selecciona un trabajador.',
            'formTipo.required'        => 'Selecciona el tipo de ausencia.',
            'formFechaInicio.required' => 'La fecha de inicio es obligatoria.',
            'formFechaFin.required'    => 'La fecha de fin es obligatoria.',
            'formFechaFin.gte'         => 'La fecha de fin no puede ser anterior al inicio.',
            'formEstadoForm.required'  => 'El estado es obligatorio.',
        ]);

        $payload = [
            'trabajador_id' => $data['formTrabajador'],
            'tipo'          => $data['formTipo'],
            'fecha_inicio'  => $data['formFechaInicio'],
            'fecha_fin'     => $data['formFechaFin'],
            'motivo'        => $data['formMotivo'] ?: null,
            'observaciones' => $data['formObservaciones'] ?: null,
        ];

        if ($this->editingId) {
            $ausencia            = Ausencia::withTrashed()->findOrFail($this->editingId);
            $payload['estado']   = $data['formEstadoForm'];
            $nuevoEstado         = EstadoAusencia::from($data['formEstadoForm']);

            if ($nuevoEstado !== EstadoAusencia::PENDIENTE && $ausencia->estado === EstadoAusencia::PENDIENTE) {
                $payload['aprobado_por'] = Auth::id();
                $payload['aprobado_at']  = now();
            }
            $ausencia->update($payload);
        } else {
            Ausencia::create(array_merge($payload, ['estado' => EstadoAusencia::PENDIENTE->value]));
        }

        $this->cerrarModal();
        $this->dispatch('toast', tipo: 'success', mensaje: $this->editingId ? 'Ausencia actualizada.' : 'Ausencia creada.');
    }

    // ── Eliminar / restaurar ─────────────────────────────────────

    public function confirmarEliminar(int $id): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas'), 403);
        $this->confirmarEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(int $id): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas'), 403);
        Ausencia::findOrFail($id)->delete();
        $this->confirmarEliminarId = null;
        $this->dispatch('toast', tipo: 'success', mensaje: 'Ausencia enviada a papelera.');
    }

    public function restaurar(int $id): void
    {
        abort_unless(Gate::allows('ausencias.ver_todas'), 403);
        Ausencia::withTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', tipo: 'success', mensaje: 'Ausencia restaurada.');
    }

    // ── Exportación ─────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function paramsExport(): array
    {
        return [
            'q'          => $this->buscar,
            'trabajador' => $this->filtroTrabajador,
            'tipo'       => $this->filtroTipo,
            'estado'     => $this->filtroEstado,
            'desde'      => $this->fechaDesde,
            'hasta'      => $this->fechaHasta,
            'papelera'   => $this->verPapelera ? 1 : 0,
            'orden'      => $this->ordenColumna,
            'dir'        => $this->ordenDireccion,
        ];
    }

    public function exportarExcel(): void
    {
        Gate::authorize('ausencias.exportar');

        $this->dispatch('descargar', url: route('ausencias.exportar.excel', $this->paramsExport()));
    }

    public function exportarPdf(string $orientacion): void
    {
        Gate::authorize('ausencias.exportar');

        if (! in_array($orientacion, ['vertical', 'horizontal'], true)) {
            abort(404);
        }

        $this->dispatch(
            'descargar',
            url: route('ausencias.exportar.pdf', array_merge(['orientacion' => $orientacion], $this->paramsExport()))
        );
    }

    // ── Render ───────────────────────────────────────────────────

    public function render(): View
    {
        $modoPapelera = $this->verPapelera && $this->puedeVerPapelera;

        $query = $modoPapelera
            ? Ausencia::onlyTrashed()->with(['trabajador', 'aprobador'])
            : Ausencia::with(['trabajador', 'aprobador']);

        if (! $modoPapelera) {
            $query
                ->when($this->filtroTrabajador, fn ($q) => $q->where('trabajador_id', $this->filtroTrabajador))
                ->when($this->filtroTipo,        fn ($q) => $q->where('tipo', $this->filtroTipo))
                ->when($this->filtroEstado,      fn ($q) => $q->where('estado', $this->filtroEstado))
                ->when($this->fechaDesde,        fn ($q) => $q->whereDate('fecha_inicio', '>=', $this->fechaDesde))
                ->when($this->fechaHasta,        fn ($q) => $q->whereDate('fecha_fin', '<=', $this->fechaHasta));
        }

        if ($this->buscar !== '') {
            $term = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('motivo', 'like', $term)
                  ->orWhere('observaciones', 'like', $term)
                  ->orWhereHas('trabajador', fn ($u) => $u->where('nombre', 'like', $term)
                      ->orWhere('apellidos', 'like', $term));
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.ausencias.index', [
            'ausencias'    => $query->paginate($this->porPagina)->onEachSide(2),
            'tipos'        => TipoAusencia::cases(),
            'estados'      => EstadoAusencia::cases(),
            'modoPapelera' => $modoPapelera,
        ]);
    }
}
