<?php

namespace App\Livewire\Forms;

use App\Models\Proyecto;
use App\Services\NumeracionService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProyectoForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $codigo = null;

    #[Validate]
    public ?int $cliente_id = null;

    #[Validate]
    public ?int $tipo_proyecto_id = null;

    #[Validate]
    public ?int $responsable_principal_id = null;

    #[Validate]
    public string $estado = 'activo';

    #[Validate]
    public ?string $fecha_inicio = null;

    #[Validate]
    public ?string $fecha_fin = null;

    #[Validate]
    public ?string $descripcion = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => [
                'required', 'string', 'max:50',
                Rule::unique('proyectos', 'codigo')
                    ->ignore($this->id)
                    ->whereNull('deleted_at'),
            ],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tipo_proyecto_id' => ['nullable', 'integer', 'exists:tipos_proyectos,id'],
            'responsable_principal_id' => ['nullable', 'integer', 'exists:users,id'],
            'estado' => ['required', Rule::in(['activo', 'inactivo', 'cerrado'])],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'nombre' => 'nombre',
            'codigo' => 'código',
            'cliente_id' => 'cliente',
            'tipo_proyecto_id' => 'tipo de proyecto',
            'responsable_principal_id' => 'responsable principal',
            'estado' => 'estado',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'descripcion' => 'descripción',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un proyecto con este código.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    public function fromModel(Proyecto $proyecto): void
    {
        $this->id = (int) $proyecto->getKey();
        $this->nombre = $proyecto->nombre;
        $this->codigo = $proyecto->codigo;
        $this->cliente_id = $proyecto->cliente_id;
        $this->tipo_proyecto_id = $proyecto->tipo_proyecto_id;
        $this->responsable_principal_id = $proyecto->responsable_principal_id;
        $this->estado = $proyecto->estado;
        $this->fecha_inicio = $proyecto->fecha_inicio
            ? Carbon::parse($proyecto->fecha_inicio)->format('Y-m-d')
            : null;
        $this->fecha_fin = $proyecto->fecha_fin
            ? Carbon::parse($proyecto->fecha_fin)->format('Y-m-d')
            : null;
        $this->descripcion = $proyecto->descripcion;
    }

    public function save(): Proyecto
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        if ($this->id === null) {
            $resultado = app(NumeracionService::class)->siguienteProyecto();
            $datos['codigo_secuencial'] = $resultado['secuencial'];
            $proyecto = new Proyecto;
        } else {
            /** @var Proyecto $proyecto */
            $proyecto = Proyecto::findOrFail($this->id);
        }

        $proyecto->fill($datos);
        $proyecto->save();

        return $proyecto;
    }
}
