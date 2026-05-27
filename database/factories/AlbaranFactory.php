<?php

namespace Database\Factories;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Albaran>
 */
class AlbaranFactory extends Factory
{
    protected $model = Albaran::class;

    public function definition(): array
    {
        $faker = fake('es_ES');

        return [
            'numero' => 'ALB-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'fecha' => $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'cliente_id' => Cliente::factory(),
            'proyecto_id' => Proyecto::factory(),
            'creado_por' => User::factory()->trabajador(),
            'responsable_id' => null,
            'estado' => EstadoAlbaran::BORRADOR,
            'tipo_hora' => $faker->randomElement([
                TipoHora::LABORAL, TipoHora::LABORAL, TipoHora::LABORAL, TipoHora::LABORAL,
                TipoHora::LABORAL, TipoHora::LABORAL,
                TipoHora::LABORAL_NOCHE, TipoHora::LABORAL_NOCHE,
                TipoHora::FESTIVO, TipoHora::FESTIVO,
                TipoHora::FESTIVO_NOCHE,
            ]),
            'observaciones' => $faker->boolean(35) ? $faker->sentence(8) : null,
            'snapshot_data' => null,
        ];
    }

    public function pendienteFirma(): static
    {
        return $this->state(fn () => ['estado' => EstadoAlbaran::PENDIENTE_FIRMA]);
    }

    public function firmado(): static
    {
        return $this->state(fn () => ['estado' => EstadoAlbaran::FIRMADO]);
    }

    public function facturado(): static
    {
        return $this->state(fn () => ['estado' => EstadoAlbaran::FACTURADO]);
    }

    public function archivado(): static
    {
        return $this->state(fn () => ['estado' => EstadoAlbaran::ARCHIVADO]);
    }
}
