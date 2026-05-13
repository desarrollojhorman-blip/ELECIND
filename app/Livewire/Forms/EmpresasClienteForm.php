<?php

namespace App\Livewire\Forms;

use App\Models\EmpresasCliente;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EmpresasClienteForm extends Form
{
    public ?int $id = null;

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
    public ?string $correo_notificaciones = null;

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
            'nombre' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'cif' => [
                'nullable', 'string', 'max:20',
                Rule::unique('empresas_clientes', 'cif')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'poblacion' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'correo_notificaciones' => ['nullable', 'email', 'max:255'],
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
            'nombre' => 'nombre',
            'nombre_comercial' => 'nombre comercial',
            'cif' => 'CIF',
            'direccion' => 'dirección',
            'codigo_postal' => 'código postal',
            'poblacion' => 'población',
            'provincia' => 'provincia',
            'telefono' => 'teléfono',
            'email' => 'email',
            'correo_notificaciones' => 'correo de notificaciones',
            'activo' => 'activo',
            'observaciones' => 'observaciones',
        ];
    }

    public function fromModel(EmpresasCliente $empresa): void
    {
        $this->id = (int) $empresa->getKey();
        $this->nombre = $empresa->nombre;
        $this->nombre_comercial = $empresa->nombre_comercial;
        $this->cif = $empresa->cif;
        $this->direccion = $empresa->direccion;
        $this->codigo_postal = $empresa->codigo_postal;
        $this->poblacion = $empresa->poblacion;
        $this->provincia = $empresa->provincia;
        $this->telefono = $empresa->telefono;
        $this->email = $empresa->email;
        $this->correo_notificaciones = $empresa->correo_notificaciones;
        $this->activo = (bool) $empresa->activo;
        $this->observaciones = $empresa->observaciones;
    }

    public function save(): EmpresasCliente
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        if ($this->id === null) {
            $empresa = new EmpresasCliente;
        } else {
            /** @var EmpresasCliente $empresa */
            $empresa = EmpresasCliente::findOrFail($this->id);
        }

        $empresa->fill($datos);
        $empresa->save();

        return $empresa;
    }
}
