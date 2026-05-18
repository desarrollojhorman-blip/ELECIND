<?php

namespace App\Livewire\Forms;

use App\Models\Cliente;
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
        return [
            'codigo_cliente' => ['required', 'string', 'max:50', Rule::unique('clientes', 'codigo_cliente')->ignore($this->id)->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'cif' => [
                'nullable', 'string', 'max:20',
                Rule::unique('clientes', 'cif')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'poblacion' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'activo' => ['boolean'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
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
            /** @var Cliente $cliente */
            $cliente = Cliente::findOrFail($this->id);
        }

        $cliente->fill($datos);
        $cliente->save();

        return $cliente;
    }
}
