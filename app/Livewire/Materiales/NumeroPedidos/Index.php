<?php

namespace App\Livewire\Materiales\NumeroPedidos;

use App\Livewire\Forms\NumeroPedidoForm;
use App\Models\Material;
use App\Models\NumeroPedido;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'pedidos'])]
#[Title('Números de Pedido')]
class Index extends Component
{
    use WithPagination;

    public NumeroPedidoForm $form;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'orden')]
    public string $ordenColumna = 'fecha';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    public bool $modalAbierto = false;

    public bool $modoSoloLectura = false;

    public ?int $confirmarEliminarId = null;

    public int $resetKey = 0;

    // ── Campos del mini-formulario para añadir materiales inline ──────
    public string $matDescripcion = '';

    public string $matUnidad = 'ud';

    public float $matStock = 0;

    /** @var array<int, array{descripcion: string, unidad_medida: string, stock: float}> */
    public array $materialesPendientes = [];

    public function mount(): void
    {
        Gate::authorize('viewAny', NumeroPedido::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['numero', 'fecha', 'proveedor'];
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

    public function abrirCrear(): void
    {
        Gate::authorize('create', NumeroPedido::class);

        $this->form->reset();
        $this->form->fecha = now()->format('Y-m-d');
        $this->materialesPendientes = [];
        $this->resetMatForm();
        $this->resetErrorBag();
        $this->modoSoloLectura = false;
        $this->modalAbierto = true;
    }

    public function abrirVer(int $id): void
    {
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::withTrashed()->findOrFail($id);
        Gate::authorize('view', $pedido);

        $this->form->fromModel($pedido);
        $this->materialesPendientes = [];
        $this->resetMatForm();
        $this->resetErrorBag();
        $this->modoSoloLectura = true;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::withTrashed()->findOrFail($id);
        Gate::authorize('update', $pedido);

        $this->form->fromModel($pedido);
        $this->materialesPendientes = [];
        $this->resetMatForm();
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
            Gate::authorize('create', NumeroPedido::class);
        } else {
            /** @var NumeroPedido $existente */
            $existente = NumeroPedido::withTrashed()->findOrFail($this->form->id);
            Gate::authorize('update', $existente);
        }

        $pedido = $this->form->save();

        // Guardar materiales pendientes añadidos inline
        foreach ($this->materialesPendientes as $mat) {
            Material::create([
                'numero_pedido_id' => $pedido->id,
                'descripcion' => $mat['descripcion'],
                'unidad_medida' => $mat['unidad_medida'],
                'stock' => $mat['stock'],
            ]);
        }

        $this->materialesPendientes = [];
        $this->modalAbierto = false;
        $this->form->reset();

        session()->flash('status', $esNuevo
            ? "Pedido «{$pedido->numero}» creado correctamente."
            : "Pedido «{$pedido->numero}» actualizado correctamente.");
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->modoSoloLectura = false;
        $this->materialesPendientes = [];
        $this->resetMatForm();
        $this->form->reset();
        $this->resetErrorBag();
    }

    // ── Inline material management ─────────────────────────────────────

    public function agregarMaterialPendiente(): void
    {
        $this->validate([
            'matDescripcion' => ['required', 'string', 'max:500'],
            'matUnidad' => ['required', 'string', 'max:20'],
            'matStock' => ['required', 'numeric', 'min:0'],
        ]);

        $this->materialesPendientes[] = [
            'descripcion' => $this->matDescripcion,
            'unidad_medida' => $this->matUnidad,
            'stock' => $this->matStock,
        ];

        $this->resetMatForm();
        $this->resetValidation(['matDescripcion', 'matUnidad', 'matStock']);
    }

    public function quitarMaterialPendiente(int $index): void
    {
        array_splice($this->materialesPendientes, $index, 1);
    }

    public function eliminarMaterialDelPedido(int $id): void
    {
        /** @var Material $material */
        $material = Material::findOrFail($id);
        Gate::authorize('delete', $material);
        $material->delete();
    }

    // ── Eliminación / restauración de pedidos ─────────────────────────

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
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::findOrFail($id);
        Gate::authorize('delete', $pedido);

        $pedido->delete();
        $this->confirmarEliminarId = null;

        session()->flash('status', "Pedido «{$pedido->numero}» enviado a papelera.");
    }

    public function restaurar(int $id): void
    {
        /** @var NumeroPedido $pedido */
        $pedido = NumeroPedido::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $pedido);

        $pedido->restore();

        session()->flash('status', "Pedido «{$pedido->numero}» restaurado.");
    }

    // ── Computed ───────────────────────────────────────────────────────

    /**
     * @return Collection<int, Material>
     */
    #[Computed]
    public function materialesDelPedidoActual(): Collection
    {
        if ($this->form->id === null) {
            return new Collection;
        }

        return Material::where('numero_pedido_id', $this->form->id)
            ->withTrashed(false)
            ->orderBy('id')
            ->get();
    }

    public function render(): View
    {
        $query = NumeroPedido::query()->withTrashed(false)->withCount('materiales');

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('numero', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino)
                    ->orWhere('proveedor', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);

        return view('livewire.materiales.numero-pedidos.index', [
            'pedidos' => $query->paginate(15),
        ]);
    }

    private function resetMatForm(): void
    {
        $this->matDescripcion = '';
        $this->matUnidad = 'ud';
        $this->matStock = 0;
    }
}
