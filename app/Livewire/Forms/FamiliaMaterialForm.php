<?php

namespace App\Livewire\Forms;

use App\Models\FamiliaMaterial;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class FamiliaMaterialForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $descripcion = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('familias_material', 'nombre')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
        ];
    }

    public function fromModel(FamiliaMaterial $familia): void
    {
        $this->id = (int) $familia->getKey();
        $this->nombre = $familia->nombre;
        $this->descripcion = $familia->descripcion;
    }

    public function save(): FamiliaMaterial
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        if ($this->id === null) {
            $familia = new FamiliaMaterial;
        } else {
            /** @var FamiliaMaterial $familia */
            $familia = FamiliaMaterial::findOrFail($this->id);
        }

        $familia->fill($datos);
        $familia->save();

        return $familia;
    }
}
