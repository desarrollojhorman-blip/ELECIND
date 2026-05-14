<?php

namespace Database\Factories;

use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlbaranLineaMaterial>
 */
class AlbaranLineaMaterialFactory extends Factory
{
    protected $model = AlbaranLineaMaterial::class;

    public function definition(): array
    {
        return [
            'albaran_id' => Albaran::factory(),
            'material_id' => Material::factory(),
            'cantidad' => fake()->randomFloat(2, 1, 20),
            'observaciones' => fake()->boolean(15) ? fake()->sentence(4) : null,
        ];
    }
}
