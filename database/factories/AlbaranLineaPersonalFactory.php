<?php

namespace Database\Factories;

use App\Models\Albaran;
use App\Models\AlbaranLineaPersonal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlbaranLineaPersonal>
 */
class AlbaranLineaPersonalFactory extends Factory
{
    protected $model = AlbaranLineaPersonal::class;

    public function definition(): array
    {
        return [
            'albaran_id' => Albaran::factory(),
            'trabajador_id' => User::factory()->trabajador(),
            'horas' => fake()->randomFloat(2, 0.5, 10),
            'horas_extra' => fake()->boolean(30) ? fake()->randomFloat(2, 0.5, 4) : 0,
            'observaciones' => fake()->boolean(20) ? fake()->sentence(5) : null,
        ];
    }
}
