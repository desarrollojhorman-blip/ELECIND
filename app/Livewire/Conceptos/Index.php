<?php

namespace App\Livewire\Conceptos;

use App\Livewire\Forms\ConceptoForm;
use App\Models\Albaran;
use App\Models\Concepto;
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

#[Layout('components.layouts.web', ['active' => 'conceptos'])]
#[Title('Conceptos')]
class Index extends Component
{
    use WithPagination;

    public ConceptoForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    /** Estados: '' (todos) | activos | inactivos */
    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    /** Modo Papelera (toggle); sobreescribe filtroEstado. */
    #[Url(as: 'papelera')]
    public bool $verPapelera = false;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'id';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public bool $panelFiltrosAbierto = false;

    public bool $modalAbierto = false;

    /** Modal abierto en modo solo lectura (acción «Ver»). */
    public bool $soloLectura = false;

    public ?int $confirmarEliminarId = null;

    /** Nº de proyectos vinculados al concepto que se va a eliminar — para el aviso del modal. */
    public int $confirmarEliminarProyectosCount = 0;

    public int $resetKey = 0;

    public function mount(): void
    {
        Gate::authorize('viewAny', Concepto::class);

        // Si llega con ?papelera=1 sin permiso, lo apaga silenciosamente.
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

    public function updatedVerPapelera(): void
    {
        if ($this->verPapelera && ! $this->puedeVerPapelera) {
            $this->verPapelera = false;

            return;
        }
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
        $this->filtroEstado = '';
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

    public function ordenarPor(string $columna): void
    {
        $columnasPermitidas = ['id', 'nombre', 'activo', 'created_at'];
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
        Gate::authorize('create', Concepto::class);

        $this->form->reset();
        $this->form->activo = true;
        $this->resetErrorBag();
        $this->soloLectura = false;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::withTrashed()->findOrFail($id);

        Gate::authorize('update', $concepto);

        $this->form->fromModel($concepto);
        $this->resetErrorBag();
        $this->soloLectura = false;
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::withTrashed()->findOrFail($id);

        Gate::authorize('view', $concepto);

        $this->form->fromModel($concepto);
        $this->resetErrorBag();
        $this->soloLectura = true;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', Concepto::class);
        } else {
            /** @var Concepto $existente */
            $existente = Concepto::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $concepto = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Concepto «{$concepto->nombre}» creado correctamente."
            : "Concepto «{$concepto->nombre}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->soloLectura = false;
        $this->form->reset();
        $this->resetErrorBag();
    }

    public function confirmarEliminar(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::withCount('proyectos')->findOrFail($id);

        // Si la policy deniega (= hay albaranes vinculados) muestra el
        // mensaje con la sugerencia de Desactivar y no abre el modal.
        $resp = Gate::inspect('delete', $concepto);
        if (! $resp->allowed()) {
            session()->flash('error', $resp->message() ?? 'No se puede eliminar este concepto.');

            return;
        }

        $this->confirmarEliminarId = $id;
        $this->confirmarEliminarProyectosCount = (int) $concepto->proyectos_count;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
        $this->confirmarEliminarProyectosCount = 0;
    }

    public function eliminar(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::findOrFail($id);

        $resp = Gate::inspect('delete', $concepto);
        if (! $resp->allowed()) {
            session()->flash('error', $resp->message() ?? 'No se puede eliminar este concepto.');
            $this->confirmarEliminarId = null;
            $this->confirmarEliminarProyectosCount = 0;

            return;
        }

        $concepto->delete();
        $this->confirmarEliminarId = null;
        $this->confirmarEliminarProyectosCount = 0;

        session()->flash('status', "Concepto «{$concepto->nombre}» enviado a papelera.");
    }

    /* ── Exportación ───────────────────────────────────────────────── */

    /** @return array<string, mixed> */
    private function paramsExport(): array
    {
        return [
            'q' => $this->buscar,
            'estado' => $this->filtroEstado,
            'papelera' => $this->verPapelera ? 1 : 0,
            'orden' => $this->ordenColumna,
            'dir' => $this->ordenDireccion,
        ];
    }

    public function exportarExcel(): void
    {
        Gate::authorize('conceptos.exportar');

        $this->dispatch(
            'descargar',
            url: route('conceptos.exportar.excel', $this->paramsExport())
        );
    }

    public function exportarPdf(string $orientacion): void
    {
        Gate::authorize('conceptos.exportar');

        if (! in_array($orientacion, ['vertical', 'horizontal'], true)) {
            abort(404);
        }

        $this->dispatch(
            'descargar',
            url: route('conceptos.exportar.pdf', array_merge(['orientacion' => $orientacion], $this->paramsExport()))
        );
    }

    /**
     * Alternativa preferida a Eliminar para conceptos con histórico:
     * desactiva (sale de selectores) sin perder vinculaciones a albaranes.
     */
    public function toggleActivo(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::findOrFail($id);
        Gate::authorize('update', $concepto);

        $concepto->activo = ! $concepto->activo;
        $concepto->save();

        session()->flash('status', $concepto->activo
            ? "Concepto «{$concepto->nombre}» activado."
            : "Concepto «{$concepto->nombre}» desactivado.");
    }

    public function restaurar(int $id): void
    {
        /** @var Concepto $concepto */
        $concepto = Concepto::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $concepto);

        $concepto->restore();

        session()->flash('status', "Concepto «{$concepto->nombre}» restaurado.");
    }

    #[Computed]
    public function albaranesDelConcepto(): Collection
    {
        if (! $this->form->id) {
            return collect();
        }

        return Albaran::where('concepto_id', $this->form->id)
            ->with(['proyecto:id,nombre', 'cliente:id,nombre'])
            ->orderBy('fecha', 'desc')
            ->get(['id', 'numero', 'fecha', 'proyecto_id', 'cliente_id', 'estado']);
    }

    #[Computed]
    public function proyectosDelConcepto(): Collection
    {
        if (! $this->form->id) {
            return collect();
        }

        return Concepto::withTrashed()->find($this->form->id)
            ?->proyectos()
            ->with('cliente:id,nombre')
            ->orderBy('nombre')
            ->get(['proyectos.id', 'nombre', 'codigo', 'cliente_id', 'estado'])
            ?? collect();
    }

    #[Computed]
    public function puedeVerPapelera(): bool
    {
        return auth()->user()?->can('conceptos.gestionar_papelera') ?? false;
    }

    #[Computed]
    public function totalPapelera(): int
    {
        return Concepto::onlyTrashed()->count();
    }

    #[Computed]
    public function filtrosAplicados(): int
    {
        // En modo papelera ignoramos el filtro Estado (el select se desactiva).
        return ! $this->verPapelera && $this->filtroEstado !== '' ? 1 : 0;
    }

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return $this->filtrosAplicados() > 0 || trim($this->buscar) !== '';
    }

    public function render(): View
    {
        $modoPapelera = $this->verPapelera && $this->puedeVerPapelera;

        $query = $modoPapelera
            ? Concepto::onlyTrashed()->withCount(['proyectos', 'albaranes'])
            : Concepto::query()->withCount(['proyectos', 'albaranes']);

        if (! $modoPapelera) {
            if ($this->filtroEstado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtroEstado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.conceptos.index', [
            'conceptos' => $query->paginate($this->porPagina)->onEachSide(2),
            'modoPapelera' => $modoPapelera,
        ]);
    }
}
