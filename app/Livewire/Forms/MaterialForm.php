<?php

namespace App\Livewire\Forms;

use App\Models\Material;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MaterialForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public ?int $numero_pedido_id = null;

    #[Validate]
    public ?int $familia_id = null;

    #[Validate]
    public string $descripcion = '';

    #[Validate]
    public string $unidad_medida = 'ud';

    #[Validate]
    public float $stock = 0;

    /** Precios (€). Aplica el permiso `materiales.gestionar_precios` para verlos/editarlos. */
    #[Validate]
    public ?string $precio_coste = null;

    #[Validate]
    public ?string $precio_venta = null;

    #[Validate]
    public bool $activo = true;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'numero_pedido_id' => ['required', 'integer', 'exists:numero_pedidos,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familias_material,id'],
            'descripcion' => ['required', 'string', 'max:500'],
            'unidad_medida' => ['required', 'string', 'max:20'],
            'stock' => ['required', 'numeric', 'min:0'],
            'precio_coste' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'precio_venta' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'numero_pedido_id' => 'número de pedido',
            'familia_id' => 'familia',
            'descripcion' => 'descripción',
            'unidad_medida' => 'unidad de medida',
            'stock' => 'stock',
            'precio_coste' => 'precio coste',
            'precio_venta' => 'precio venta',
            'activo' => 'activo',
        ];
    }

    public function fromModel(Material $material): void
    {
        $this->id = (int) $material->getKey();
        $this->numero_pedido_id = $material->numero_pedido_id;
        $this->familia_id = $material->familia_id;
        $this->descripcion = $material->descripcion;
        $this->unidad_medida = $material->unidad_medida;
        $this->stock = (float) $material->stock;
        $this->precio_coste = $material->precio_coste !== null ? (string) $material->precio_coste : null;
        $this->precio_venta = $material->precio_venta !== null ? (string) $material->precio_venta : null;
        $this->activo = (bool) $material->activo;
    }

    public function save(): Material
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        // Precios: '' → null, string con coma decimal → float.
        $datos['precio_coste'] = $this->precio_coste === null || $this->precio_coste === ''
            ? null
            : (float) str_replace(',', '.', $this->precio_coste);
        $datos['precio_venta'] = $this->precio_venta === null || $this->precio_venta === ''
            ? null
            : (float) str_replace(',', '.', $this->precio_venta);
        $datos['activo'] = $this->activo;

        if ($this->id === null) {
            $material = new Material;
        } else {
            /** @var Material $material */
            $material = Material::findOrFail($this->id);
        }

        $material->fill($datos);
        $material->save();

        return $material;
    }
}
