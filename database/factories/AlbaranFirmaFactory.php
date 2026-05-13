<?php

namespace Database\Factories;

use App\Enums\TipoFirma;
use App\Models\Albaran;
use App\Models\AlbaranFirma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlbaranFirma>
 */
class AlbaranFirmaFactory extends Factory
{
    protected $model = AlbaranFirma::class;

    public function definition(): array
    {
        return [
            'albaran_id' => Albaran::factory(),
            'tipo' => fake()->randomElement(TipoFirma::cases()),
            'firmado_por_user_id' => null,
            'token_id' => null,
            'firma_path' => 'firmas/demo-'.fake()->uuid().'.png',
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'geolocalizacion' => null,
            'firmado_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function trabajador(): static
    {
        return $this->state(fn () => ['tipo' => TipoFirma::TRABAJADOR]);
    }

    public function responsable(): static
    {
        return $this->state(fn () => ['tipo' => TipoFirma::RESPONSABLE]);
    }
}
