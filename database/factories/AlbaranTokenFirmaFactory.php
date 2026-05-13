<?php

namespace Database\Factories;

use App\Enums\TipoFirma;
use App\Models\Albaran;
use App\Models\AlbaranTokenFirma;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AlbaranTokenFirma>
 */
class AlbaranTokenFirmaFactory extends Factory
{
    protected $model = AlbaranTokenFirma::class;

    public function definition(): array
    {
        return [
            'albaran_id' => Albaran::factory(),
            'tipo_firmante' => fake()->randomElement(TipoFirma::cases()),
            'token' => Str::random(64),
            'email_destino' => fake()->safeEmail(),
            'nombre_destino' => fake()->name(),
            'caduca_at' => now()->addDays(7),
            'usado_at' => null,
            'invalidado_at' => null,
            'reemplazado_por_token_id' => null,
            'generado_por_user_id' => null,
        ];
    }

    public function caducado(): static
    {
        return $this->state(fn () => ['caduca_at' => now()->subDay()]);
    }

    public function usado(): static
    {
        return $this->state(fn () => ['usado_at' => now()->subHour()]);
    }

    public function invalidado(): static
    {
        return $this->state(fn () => ['invalidado_at' => now()->subHour()]);
    }
}
