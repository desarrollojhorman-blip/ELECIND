<?php

namespace App\Livewire\Forms;

use App\Models\TiposProyecto;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class GrupoProyectoForm extends Form
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
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_proyectos', 'nombre')
                    ->ignore($this->id)
                    ->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'nombre' => 'grupo',
            'descripcion' => 'descripcion',
            'activo' => 'estado',
        ];
    }

    public function fromModel(TiposProyecto $grupo): void
    {
        $this->id = (int) $grupo->getKey();
        $this->nombre = $grupo->nombre;
        $this->descripcion = $grupo->descripcion;
        $this->activo = (bool) $grupo->activo;
    }

    public function save(): TiposProyecto
    {
        $this->validate();

        if ($this->id === null) {
            $grupo = new TiposProyecto;
        } else {
            /** @var TiposProyecto $grupo */
            $grupo = TiposProyecto::withTrashed()->findOrFail($this->id);
        }

        $grupo->fill([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);
        $grupo->save();

        return $grupo;
    }
}
