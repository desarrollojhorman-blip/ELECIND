<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\MaterialLote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialLote>
 */
class MaterialLoteFactory extends Factory
{
    protected $model = MaterialLote::class;

    public function definition(): array
    {
        $faker = fake('es_ES');
        $stockInicial = $faker->randomFloat(2, 20, 500);
        $consumido = $faker->randomFloat(2, 0, (float) $stockInicial * 0.6);

        return [
            'material_id' => Material::factory(),
            'codigo_lote' => $faker->unique()->bothify('LOT-####-??'),
            'proveedor' => $faker->company(),
            'n_pedido' => $faker->bothify('PED-####/##'),
            'stock_inicial' => $stockInicial,
            'stock_disponible' => round($stockInicial - $consumido, 2),
            'fecha_entrada' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'fecha_caducidad' => $faker->boolean(20)
                ? $faker->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d')
                : null,
            'stock_minimo_lote' => $faker->randomElement([0, 5, 10]),
        ];
    }

    public function agotado(): static
    {
        return $this->state(fn (array $attrs) => [
            'stock_disponible' => 0,
        ]);
    }
}
