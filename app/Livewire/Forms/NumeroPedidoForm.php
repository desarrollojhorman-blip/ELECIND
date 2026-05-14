<?php

namespace App\Livewire\Forms;

use App\Models\NumeroPedido;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class NumeroPedidoForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public string $numero = '';

    #[Validate]
    public ?string $descripcion = null;

    #[Validate]
    public string $fecha = '';

    #[Validate]
    public ?string $proveedor = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'numero' => [
                'required', 'string', 'max:100',
                Rule::unique('numero_pedidos', 'numero')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'fecha' => ['required', 'date'],
            'proveedor' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'numero' => 'número de pedido',
            'descripcion' => 'descripción',
            'fecha' => 'fecha',
            'proveedor' => 'proveedor',
        ];
    }

    public function fromModel(NumeroPedido $pedido): void
    {
        $this->id = (int) $pedido->getKey();
        $this->numero = $pedido->numero;
        $this->descripcion = $pedido->descripcion;
        $this->fecha = $pedido->fecha->format('Y-m-d');
        $this->proveedor = $pedido->proveedor;
    }

    public function save(): NumeroPedido
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        if ($this->id === null) {
            $pedido = new NumeroPedido;
        } else {
            /** @var NumeroPedido $pedido */
            $pedido = NumeroPedido::findOrFail($this->id);
        }

        $pedido->fill($datos);
        $pedido->save();

        return $pedido;
    }
}
