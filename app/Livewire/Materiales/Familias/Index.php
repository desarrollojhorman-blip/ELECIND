<?php

namespace App\Livewire\Materiales\Familias;

use App\Livewire\Forms\FamiliaMaterialForm;
use App\Models\FamiliaMaterial;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'familias'])]
#[Title('Familias de Material')]
class Index extends Component
{
    use WithPagination;

    public FamiliaMaterialForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'nombre';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    public bool $modalAbierto = false;

    public bool $modoSoloLectura = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    // ── Modal "Asignar materiales" ────────────────────────────────────
    public bool $modalAsignarAbierto = false;

    public string $buscarAsignar = '';

    /**
     * Toggle UX (opción C): por defecto solo materiales sin familia,
     * activable para reasignar materiales que ya tengan otra familia.
     */
    public bool $mostrarTodosAsignar = false;

    /** @var array<int, int> ids de materiales seleccionados en el modal asignar */
    public array $materialesSeleccionados = [];

    public function mount(): void
    {
        Gate::authorize('viewAny', FamiliaMaterial::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['id', 'nombre', 'descripcion'];
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

    public function limpiarBuscador(): void
    {
        $this->buscar = '';
        $this->resetPage();
        $this->resetKey++;
    }

    // ── CRUD Familia ──────────────────────────────────────────────────

    public function abrirCrear(): void
    {
        Gate::authorize('create', FamiliaMaterial::class);

        $this->form->reset();
        $this->resetErrorBag();
        $this->modoSoloLectura = false;
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::withTrashed()->findOrFail($id);
        Gate::authorize('view', $familia);

        $this->form->fromModel($familia);
        $this->resetErrorBag();
        $this->modoSoloLectura = true;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::withTrashed()->findOrFail($id);
        Gate::authorize('update', $familia);

        $this->form->fromModel($familia);
        $this->resetErrorBag();
        $this->modoSoloLectura = false;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        if ($this->modoSoloLectura) {
            abort(403);
        }

        $esNuevo = $this->form->id === null;

        if ($esNuevo) {
            Gate::authorize('create', FamiliaMaterial::class);
        } else {
            /** @var FamiliaMaterial $existente */
            $existente = FamiliaMaterial::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $familia = $this->form->save();

        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Familia «{$familia->nombre}» creada correctamente."
            : "Familia «{$familia->nombre}» actualizada correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modoSoloLectura = false;
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
        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::findOrFail($id);
        Gate::authorize('delete', $familia);

        $familia->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Familia «{$familia->nombre}» enviada a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $familia);

        $familia->restore();

        session()->flash('status', "Familia «{$familia->nombre}» restaurada.");
    }

    // ── Modal "Asignar materiales a esta familia" ─────────────────────

    public function abrirModalAsignar(): void
    {
        if ($this->form->id === null) {
            return;
        }

        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::findOrFail($this->form->id);
        Gate::authorize('update', $familia);

        $this->buscarAsignar = '';
        $this->mostrarTodosAsignar = false;
        $this->materialesSeleccionados = [];
        $this->modalAsignarAbierto = true;
    }

    public function cerrarModalAsignar(): void
    {
        $this->modalAsignarAbierto = false;
        $this->materialesSeleccionados = [];
        $this->buscarAsignar = '';
    }

    public function asignarSeleccionados(): void
    {
        if ($this->form->id === null) {
            return;
        }

        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::findOrFail($this->form->id);
        Gate::authorize('update', $familia);

        if ($this->materialesSeleccionados === []) {
            $this->cerrarModalAsignar();

            return;
        }

        $ids = array_values(array_unique(array_map('intval', $this->materialesSeleccionados)));

        $cuantos = Material::query()
            ->whereIn('id', $ids)
            ->update(['familia_id' => $familia->id]);

        $this->cerrarModalAsignar();
        unset($this->materialesDeLaFamiliaActual);

        session()->flash(
            'status',
            $cuantos === 1
                ? "1 material asignado a «{$familia->nombre}»."
                : "{$cuantos} materiales asignados a «{$familia->nombre}»."
        );
    }

    public function quitarMaterialDeFamilia(int $materialId): void
    {
        if ($this->form->id === null) {
            return;
        }

        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::findOrFail($this->form->id);
        Gate::authorize('update', $familia);

        Material::query()
            ->where('id', $materialId)
            ->where('familia_id', $familia->id)
            ->update(['familia_id' => null]);

        unset($this->materialesDeLaFamiliaActual);
    }

    // ── Computed ──────────────────────────────────────────────────────

    #[Computed]
    public function tieneAlgoQueLimpiar(): bool
    {
        return trim($this->buscar) !== '';
    }

    /**
     * Materiales que ya pertenecen a la familia abierta en el modal Ver/Editar.
     *
     * @return EloquentCollection<int, Material>
     */
    #[Computed(persist: false)]
    public function materialesDeLaFamiliaActual(): EloquentCollection
    {
        if ($this->form->id === null) {
            return new EloquentCollection;
        }

        return Material::query()
            ->where('familia_id', $this->form->id)
            ->with('numeroPedido:id,numero')
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'unidad_medida', 'stock', 'numero_pedido_id', 'familia_id']);
    }

    /**
     * Materiales asignables en el modal "Asignar materiales".
     * Por defecto: solo huérfanos (familia_id IS NULL).
     * Con $mostrarTodosAsignar: incluye los que ya tienen otra familia (no la actual).
     *
     * @return EloquentCollection<int, Material>
     */
    #[Computed(persist: false)]
    public function materialesAsignables(): EloquentCollection
    {
        if ($this->form->id === null) {
            return new EloquentCollection;
        }

        $familiaId = (int) $this->form->id;

        $query = Material::query()
            ->with(['numeroPedido:id,numero', 'familia:id,nombre']);

        if ($this->mostrarTodosAsignar) {
            $query->where(function (Builder $q) use ($familiaId): void {
                $q->whereNull('familia_id')->orWhere('familia_id', '!=', $familiaId);
            });
        } else {
            $query->whereNull('familia_id');
        }

        if ($this->buscarAsignar !== '') {
            $termino = '%'.trim($this->buscarAsignar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('descripcion', 'like', $termino)
                    ->orWhere('unidad_medida', 'like', $termino)
                    ->orWhereHas('numeroPedido', fn ($q2) => $q2->where('numero', 'like', $termino));
            });
        }

        return $query
            ->orderBy('descripcion')
            ->limit(100)
            ->get(['id', 'descripcion', 'unidad_medida', 'stock', 'numero_pedido_id', 'familia_id']);
    }

    public function render(): View
    {
        $query = FamiliaMaterial::query()->withCount('materiales');

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.materiales.familias.index', [
            'familias' => $query->paginate(15),
        ]);
    }
}
