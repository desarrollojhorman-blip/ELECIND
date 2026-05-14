<?php

namespace Database\Factories;

use App\Models\NumeroPedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NumeroPedido>
 */
class NumeroPedidoFactory extends Factory
{
    protected $model = NumeroPedido::class;

    public function definition(): array
    {
        return [
            'numero' => fake()->unique()->bothify('PED-####'),
            'descripcion' => fake()->boolean(60) ? fake()->sentence(5) : null,
            'fecha' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'proveedor' => fake()->boolean(70) ? fake()->company() : null,
        ];
    }
}
