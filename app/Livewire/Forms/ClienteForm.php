<?php

namespace App\Livewire\Forms;

use App\Models\Cliente;
use App\Support\ClienteFields;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ClienteForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public ?string $codigo_cliente = null;

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $nombre_comercial = null;

    #[Validate]
    public ?string $cif = null;

    #[Validate]
    public ?string $direccion = null;

    #[Validate]
    public ?string $codigo_postal = null;

    #[Validate]
    public ?string $poblacion = null;

    #[Validate]
    public ?string $provincia = null;

    #[Validate]
    public ?string $telefono = null;

    #[Validate]
    public ?string $email = null;

    #[Validate]
    public bool $activo = true;

    #[Validate]
    public ?string $observaciones = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // Reglas base centralizadas en ClienteFields (fuente única de verdad).
        $rules = ClienteFields::getValidationRules();

        // Únicos dinámicos: QUÉ campos son únicos vive en ClienteFields
        // (fuente única, compartida con la importación). Aquí solo se arma la
        // regla, que depende del registro en edición + soft-delete.
        foreach (ClienteFields::uniqueFields() as $campo) {
            $rules[$campo][] = Rule::unique('clientes', $campo)
                ->ignore($this->id)->whereNull('deleted_at');
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max' => 'El campo :attribute no puede superar :max.',
            'email' => 'El :attribute no tiene un formato válido.',
            'unique' => 'Ese :attribute ya está en uso.',
            'boolean' => 'El campo :attribute no es válido.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'codigo_cliente' => 'código cliente',
            'nombre' => 'nombre',
            'nombre_comercial' => 'nombre comercial',
            'cif' => 'CIF',
            'direccion' => 'dirección',
            'codigo_postal' => 'código postal',
            'poblacion' => 'población',
            'provincia' => 'provincia',
            'telefono' => 'teléfono',
            'email' => 'email',
            'activo' => 'activo',
            'observaciones' => 'observaciones',
        ];
    }

    public function fromModel(Cliente $cliente): void
    {
        $this->id = (int) $cliente->getKey();
        $this->codigo_cliente = $cliente->codigo_cliente;
        $this->nombre = $cliente->nombre;
        $this->nombre_comercial = $cliente->nombre_comercial;
        $this->cif = $cliente->cif;
        $this->direccion = $cliente->direccion;
        $this->codigo_postal = $cliente->codigo_postal;
        $this->poblacion = $cliente->poblacion;
        $this->provincia = $cliente->provincia;
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email;
        $this->activo = (bool) $cliente->activo;
        $this->observaciones = $cliente->observaciones;
    }

    public function save(): Cliente
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        if ($this->id === null) {
            $cliente = new Cliente;
        } else {
            // Inmutable tras crear: el código no se modifica nunca al editar,
            // aunque se manipule el formulario (salvaguarda backend).
            unset($datos['codigo_cliente']);

            /** @var Cliente $cliente */
            $cliente = Cliente::findOrFail($this->id);
        }

        $cliente->fill($datos);
        $cliente->save();

        return $cliente;
    }
}
