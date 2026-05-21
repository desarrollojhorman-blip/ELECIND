<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\NumeroPedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        $articulos = [
            ['Cable H07V-K 1,5mm² azul', 'm'],
            ['Cable H07V-K 2,5mm² negro', 'm'],
            ['Cable RZ1-K 5G6mm²', 'm'],
            ['Interruptor simple blanco', 'ud'],
            ['Conmutador blanco', 'ud'],
            ['Base de enchufe schuko', 'ud'],
            ['Magnetotérmico 1P+N 10A', 'ud'],
            ['Magnetotérmico 1P+N 16A', 'ud'],
            ['Diferencial 2P 25A 30mA', 'ud'],
            ['Downlight LED 12W', 'ud'],
            ['Panel LED 60x60', 'ud'],
            ['Tubo corrugado Ø20mm', 'm'],
            ['Canal 60x40 blanca', 'm'],
            ['Caja empalmes 100x100', 'ud'],
            ['Cuadro 12 módulos empotrar', 'ud'],
        ];

        [$descripcion, $unidad] = fake()->randomElement($articulos);

        // Precio coste entre 0,50 € y 80 €. Precio venta con margen 1,3×–2,2×.
        $coste = fake()->randomFloat(2, 0.5, 80);
        $venta = round($coste * fake()->randomFloat(2, 1.3, 2.2), 2);

        return [
            'numero_pedido_id' => NumeroPedido::factory(),
            'descripcion' => $descripcion.' '.fake()->bothify('#?'),
            'unidad_medida' => $unidad,
            'stock' => fake()->randomFloat(2, 0, 500),
            'precio_coste' => $coste,
            'precio_venta' => $venta,
        ];
    }
}
