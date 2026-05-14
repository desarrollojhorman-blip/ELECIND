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

    /** Id del material seleccionado en el select para añadir a la familia. */
    public ?int $materialAAsignar = null;

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
        $this->materialAAsignar = null;
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
        $this->materialAAsignar = null;
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
        $this->materialAAsignar = null;
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
        $this->materialAAsignar = null;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Familia «{$familia->nombre}» creada correctamente."
            : "Familia «{$familia->nombre}» actualizada correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modoSoloLectura = false;
        $this->materialAAsignar = null;
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

    // ── Asignación inmediata de materiales a la familia ────────────────

    /**
     * Asigna el material seleccionado a la familia abierta (en modo edición).
     * Acción inmediata sobre BD: solo válido si la familia ya existe.
     */
    public function agregarMaterialAFamilia(): void
    {
        if ($this->form->id === null || $this->materialAAsignar === null) {
            return;
        }

        /** @var FamiliaMaterial $familia */
        $familia = FamiliaMaterial::findOrFail($this->form->id);
        Gate::authorize('update', $familia);

        Material::query()
            ->where('id', $this->materialAAsignar)
            ->whereNull('familia_id')
            ->update(['familia_id' => $familia->id]);

        $this->materialAAsignar = null;
        unset($this->materialesDeLaFamiliaActual, $this->materialesHuerfanos);
    }

    /**
     * Quita un material asignado a la familia (acción inmediata sobre BD).
     */
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

        unset($this->materialesDeLaFamiliaActual, $this->materialesHuerfanos);
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
     * Materiales sin familia disponibles para asignar a la familia abierta.
     *
     * @return EloquentCollection<int, Material>
     */
    #[Computed(persist: false)]
    public function materialesHuerfanos(): EloquentCollection
    {
        return Material::query()
            ->whereNull('familia_id')
            ->with('numeroPedido:id,numero')
            ->orderBy('descripcion')
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
