<?php

namespace App\Livewire\Pedidos;

use App\Livewire\Forms\NumeroPedidoForm;
use App\Models\AlbaranLineaMaterial;
use App\Models\FamiliaMaterial;
use App\Models\Material;
use App\Models\NumeroPedido;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Página dedicada para Crear/Editar Pedidos (full-page, no modal).
 *
 * Flujo "documento entero o nada": el usuario añade/edita/quita líneas de
 * materiales en memoria y, al pulsar Guardar, se persiste todo en una sola
 * transacción. Si algo falla → rollback total.
 */
#[Layout('components.layouts.web', ['active' => 'pedidos'])]
class Editar extends Component
{
    public NumeroPedidoForm $form;

    public ?NumeroPedido $pedido = null;

    /**
     * Líneas de materiales (existentes + nuevas en memoria).
     * Cada item: {id?, descripcion, unidad_medida, stock, familia_id?, precio_coste?, precio_venta?}
     *
     * @var array<int, array<string, mixed>>
     */
    public array $lineas = [];

    /**
     * IDs de materiales existentes marcados para eliminar al guardar.
     *
     * @var array<int, int>
     */
    public array $lineasAEliminar = [];

    public ?int $confirmarEliminarId = null;

    public function mount(?NumeroPedido $pedido = null): void
    {
        if ($pedido !== null && $pedido->exists) {
            Gate::authorize('update', $pedido);
            $this->pedido = $pedido;
            $this->form->fromModel($pedido);
            $this->cargarLineasExistentes();
        } else {
            Gate::authorize('create', NumeroPedido::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    private function cargarLineasExistentes(): void
    {
        if ($this->pedido === null) {
            return;
        }

        $materiales = Material::query()
            ->where('numero_pedido_id', $this->pedido->id)
            ->orderBy('id')
            ->get();

        $this->lineas = $materiales->map(fn (Material $m): array => [
            'id' => $m->id,
            'descripcion' => $m->descripcion,
            'unidad_medida' => $m->unidad_medida,
            'stock' => (string) $m->stock,
            'familia_id' => $m->familia_id,
            'precio_coste' => $m->precio_coste !== null ? (string) $m->precio_coste : '',
            'precio_venta' => $m->precio_venta !== null ? (string) $m->precio_venta : '',
        ])->all();
    }

    /* ── Manipulación de líneas (en memoria) ───────────────────────── */

    /**
     * Método sin ñ a propósito — Livewire tiene problemas resolviendo
     * nombres de método con caracteres no-ASCII en `wire:click`.
     */
    public function agregarLinea(): void
    {
        Gate::authorize('create', Material::class);

        $this->lineas[] = [
            'id' => null,
            'descripcion' => '',
            'unidad_medida' => 'ud',
            'stock' => '0',
            'familia_id' => null,
            'precio_coste' => '',
            'precio_venta' => '',
        ];
    }

    public function quitarLinea(int $index): void
    {
        if (! isset($this->lineas[$index])) {
            return;
        }

        $linea = $this->lineas[$index];

        // Si tiene id (es material existente) → marcar para eliminar al guardar.
        if (! empty($linea['id'])) {
            // Bloqueo de integridad: si el material está en albaranes, NO permitir borrar.
            $usadoEnAlbaranes = AlbaranLineaMaterial::query()
                ->where('material_id', $linea['id'])
                ->exists();

            if ($usadoEnAlbaranes) {
                $this->addError('lineas.'.$index, 'No puedes eliminar este material: está usado en uno o más albaranes.');

                return;
            }

            $this->lineasAEliminar[] = (int) $linea['id'];
        }

        array_splice($this->lineas, $index, 1);
    }

    /* ── Guardar / Cancelar ─────────────────────────────────────────── */

    public function guardar(): void
    {
        $esNuevo = $this->pedido === null;

        if ($esNuevo) {
            Gate::authorize('create', NumeroPedido::class);
        } else {
            Gate::authorize('update', $this->pedido);
        }

        // Validar cabecera.
        $this->form->validate();

        // Validar líneas (al menos descripción, unidad y stock).
        foreach ($this->lineas as $i => $linea) {
            if (trim((string) ($linea['descripcion'] ?? '')) === '') {
                $this->addError('lineas.'.$i.'.descripcion', 'La descripción del material es obligatoria.');

                return;
            }
            if (trim((string) ($linea['unidad_medida'] ?? '')) === '') {
                $this->addError('lineas.'.$i.'.unidad_medida', 'La unidad de medida es obligatoria.');

                return;
            }
            if (! is_numeric($linea['stock'] ?? null) || (float) $linea['stock'] < 0) {
                $this->addError('lineas.'.$i.'.stock', 'El stock debe ser un número ≥ 0.');

                return;
            }
        }

        // Persistir todo en una transacción.
        try {
            DB::transaction(function (): void {
                $pedido = $this->form->save();
                $this->pedido = $pedido;

                // Eliminar materiales marcados.
                if ($this->lineasAEliminar !== []) {
                    foreach ($this->lineasAEliminar as $idEliminar) {
                        $material = Material::find($idEliminar);
                        if ($material !== null) {
                            $material->delete();
                        }
                    }
                    $this->lineasAEliminar = [];
                }

                // Crear/actualizar materiales de cada línea.
                foreach ($this->lineas as $i => $linea) {
                    $datos = [
                        'numero_pedido_id' => $pedido->id,
                        'familia_id' => $linea['familia_id'] ?: null,
                        'descripcion' => trim((string) $linea['descripcion']),
                        'unidad_medida' => trim((string) $linea['unidad_medida']),
                        'stock' => (float) str_replace(',', '.', (string) $linea['stock']),
                        'precio_coste' => trim((string) ($linea['precio_coste'] ?? '')) === ''
                            ? null
                            : (float) str_replace(',', '.', (string) $linea['precio_coste']),
                        'precio_venta' => trim((string) ($linea['precio_venta'] ?? '')) === ''
                            ? null
                            : (float) str_replace(',', '.', (string) $linea['precio_venta']),
                    ];

                    if (! empty($linea['id'])) {
                        $material = Material::find($linea['id']);
                        if ($material !== null) {
                            $material->fill($datos);
                            $material->save();
                            $this->lineas[$i]['id'] = $material->id;
                        }
                    } else {
                        $material = Material::create($datos);
                        $this->lineas[$i]['id'] = $material->id;
                    }
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Error guardando pedido', ['error' => $e->getMessage()]);
            $this->addError('form.numero', 'No se pudo guardar el pedido: '.$e->getMessage());

            return;
        }

        session()->flash('status', $esNuevo
            ? "Pedido «{$this->pedido->numero}» creado correctamente."
            : "Pedido «{$this->pedido->numero}» actualizado correctamente.");

        $this->redirectRoute('pedidos.editar', ['pedido' => $this->pedido->getKey()], navigate: true);
    }

    public function deshacer(): void
    {
        if ($this->pedido !== null) {
            $this->form->fromModel($this->pedido);
            $this->cargarLineasExistentes();
        } else {
            $this->form->reset();
            $this->form->fecha = now()->format('Y-m-d');
            $this->lineas = [];
        }
        $this->lineasAEliminar = [];
        $this->resetErrorBag();
    }

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->pedido);
        $this->confirmarEliminarId = $this->pedido?->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->pedido);

        $numero = $this->pedido->numero;
        $this->pedido->delete();
        session()->flash('status', "Pedido «{$numero}» enviado a papelera.");
        $this->redirectRoute('materiales.pedidos', navigate: false);
    }

    /* ── Computeds ─────────────────────────────────────────────────── */

    /** @return Collection<int, FamiliaMaterial> */
    #[Computed]
    public function familiasDisponibles(): Collection
    {
        return FamiliaMaterial::query()->orderBy('nombre')->get(['id', 'nombre']);
    }

    /**
     * Consumo de cada material del pedido.
     * Tab "Consumo": para cada material, cuánto se ha gastado en albaranes
     * (no en papelera) y cuánto queda (stock actual). Solo se calcula para
     * pedidos ya guardados.
     *
     * @return EloquentCollection<int, Material>
     */
    #[Computed]
    public function materialesConConsumo(): EloquentCollection
    {
        if ($this->pedido === null) {
            return new EloquentCollection;
        }

        return Material::query()
            ->where('numero_pedido_id', $this->pedido->id)
            ->withSum(['lineasAlbaran as cantidad_consumida' => function ($q): void {
                $q->whereHas('albaran', fn ($q2) => $q2->whereNull('deleted_at'));
            }], 'cantidad')
            ->orderBy('descripcion')
            ->get();
    }

    public function render(): View
    {
        $titulo = $this->pedido ? 'Editar pedido' : 'Nuevo pedido';

        return view('livewire.pedidos.editar', compact('titulo'));
    }
}
