<?php

namespace App\Livewire\Forms;

use App\Models\Material;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MaterialForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public ?string $codigo = null;

    #[Validate]
    public ?string $grupo = null;

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $descripcion = null;

    #[Validate]
    public string $unidad_medida = 'ud';

    #[Validate]
    public float $stock_minimo = 0;

    #[Validate]
    public bool $notificar_stock_bajo = true;

    #[Validate]
    public bool $activo = true;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'codigo' => [
                'nullable', 'string', 'max:255',
                Rule::unique('materiales', 'codigo')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'grupo' => ['nullable', 'string', 'max:255'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'unidad_medida' => ['required', 'string', 'max:20'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
            'notificar_stock_bajo' => ['boolean'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'codigo' => 'código',
            'grupo' => 'grupo',
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'unidad_medida' => 'unidad de medida',
            'stock_minimo' => 'stock mínimo',
            'notificar_stock_bajo' => 'notificar stock bajo',
            'activo' => 'activo',
        ];
    }

    public function fromModel(Material $material): void
    {
        $this->id = (int) $material->getKey();
        $this->codigo = $material->codigo;
        $this->grupo = $material->grupo;
        $this->nombre = $material->nombre;
        $this->descripcion = $material->descripcion;
        $this->unidad_medida = $material->unidad_medida;
        $this->stock_minimo = (float) $material->stock_minimo;
        $this->notificar_stock_bajo = (bool) $material->notificar_stock_bajo;
        $this->activo = (bool) $material->activo;
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
