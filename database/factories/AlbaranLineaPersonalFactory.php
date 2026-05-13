<?php

namespace Database\Factories;

use App\Enums\TipoHora;
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
            'tipo_hora' => fake()->randomElement(TipoHora::cases()),
            'horas' => fake()->randomFloat(2, 0.5, 10),
            'observaciones' => fake()->boolean(20) ? fake()->sentence(5) : null,
        ];
    }
}
