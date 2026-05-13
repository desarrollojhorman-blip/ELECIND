<?php

namespace App\Livewire\Forms;

use App\Models\TiposProyecto;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Formulario reducido para crear un Tipo de Proyecto al vuelo desde
 * el modal de Proyecto. Solo nombre + descripción. Si en el futuro
 * los tipos ganan más campos editables, este form sigue siendo el
 * "rápido" mientras que la edición completa se hace desde otra UI.
 */
class TipoProyectoQuickForm extends Form
{
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
                Rule::unique('tipos_proyectos', 'nombre')->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
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

    public function save(): TiposProyecto
    {
        $this->validate();

        $tipo = new TiposProyecto;
        $tipo->fill([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => true,
        ]);
        $tipo->save();

        return $tipo;
    }
}
