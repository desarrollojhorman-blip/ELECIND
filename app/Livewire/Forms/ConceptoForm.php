<?php

namespace App\Livewire\Forms;

use App\Models\Concepto;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ConceptoForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $descripcion = null;

    #[Validate]
    public bool $activo = true;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('conceptos', 'nombre')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],
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
            'activo' => 'activo',
        ];
    }

    public function fromModel(Concepto $concepto): void
    {
        $this->id = (int) $concepto->getKey();
        $this->nombre = $concepto->nombre;
        $this->descripcion = $concepto->descripcion;
        $this->activo = (bool) $concepto->activo;
    }

    public function save(): Concepto
    {
        $this->validate();

        $datos = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ];

        if ($this->id === null) {
            $concepto = new Concepto;
        } else {
            /** @var Concepto $concepto */
            $concepto = Concepto::findOrFail($this->id);
        }

        $concepto->fill($datos);
        $concepto->save();

        return $concepto;
    }
}
