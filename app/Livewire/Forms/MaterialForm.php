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
    }

    public function save(): Material
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

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
