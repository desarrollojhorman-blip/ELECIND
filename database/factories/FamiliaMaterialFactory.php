<?php

namespace Database\Factories;

use App\Models\FamiliaMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamiliaMaterial>
 */
class FamiliaMaterialFactory extends Factory
{
    protected $model = FamiliaMaterial::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true).' '.fake()->randomNumber(3),
            'descripcion' => fake()->boolean(60) ? fake()->sentence(6) : null,
        ];
    }
}
