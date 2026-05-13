<?php

namespace App\Livewire\Forms;

use App\Models\MaterialLote;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MaterialLoteForm extends Form
{
    public ?int $id = null;

    public ?int $material_id = null;

    #[Validate]
    public ?string $codigo_lote = null;

    #[Validate]
    public ?string $proveedor = null;

    #[Validate]
    public ?string $n_pedido = null;

    #[Validate]
    public float $stock_inicial = 0;

    #[Validate]
    public float $stock_disponible = 0;

    #[Validate]
    public ?string $fecha_entrada = null;

    #[Validate]
    public ?string $fecha_caducidad = null;

    #[Validate]
    public float $stock_minimo_lote = 0;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'codigo_lote' => [
                'nullable', 'string', 'max:255',
                Rule::unique('material_lotes', 'codigo_lote')->ignore($this->id)->whereNull('deleted_at'),
            ],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'n_pedido' => ['nullable', 'string', 'max:255'],
            'stock_inicial' => ['required', 'numeric', 'min:0'],
            'stock_disponible' => ['required', 'numeric', 'min:0', 'lte:stock_inicial'],
            'fecha_entrada' => ['nullable', 'date'],
            'fecha_caducidad' => ['nullable', 'date', 'after_or_equal:fecha_entrada'],
            'stock_minimo_lote' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'codigo_lote' => 'código de lote',
            'proveedor' => 'proveedor',
            'n_pedido' => 'nº pedido',
            'stock_inicial' => 'stock inicial',
            'stock_disponible' => 'stock disponible',
            'fecha_entrada' => 'fecha de entrada',
            'fecha_caducidad' => 'fecha de caducidad',
            'stock_minimo_lote' => 'stock mínimo del lote',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'stock_disponible.lte' => 'El stock disponible no puede superar el stock inicial.',
            'fecha_caducidad.after_or_equal' => 'La fecha de caducidad debe ser igual o posterior a la fecha de entrada.',
        ];
    }

    public function fromModel(MaterialLote $lote): void
    {
        $this->id = (int) $lote->getKey();
        $this->material_id = $lote->material_id;
        $this->codigo_lote = $lote->codigo_lote;
        $this->proveedor = $lote->proveedor;
        $this->n_pedido = $lote->n_pedido;
        $this->stock_inicial = (float) $lote->stock_inicial;
        $this->stock_disponible = (float) $lote->stock_disponible;
        $this->fecha_entrada = $lote->fecha_entrada
            ? Carbon::parse($lote->fecha_entrada)->format('Y-m-d')
            : null;
        $this->fecha_caducidad = $lote->fecha_caducidad
            ? Carbon::parse($lote->fecha_caducidad)->format('Y-m-d')
            : null;
        $this->stock_minimo_lote = (float) $lote->stock_minimo_lote;
    }

    public function save(): MaterialLote
    {
        $this->validate();

        $datos = $this->all();
        unset($datos['id']);

        if ($this->id === null) {
            $lote = new MaterialLote;
        } else {
            /** @var MaterialLote $lote */
            $lote = MaterialLote::findOrFail($this->id);
        }

        $lote->fill($datos);
        $lote->save();

        return $lote;
    }
}
