<?php

namespace Database\Factories;

use App\Enums\EstadoAusencia;
use App\Enums\TipoAusencia;
use App\Models\Ausencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ausencia> */
class AusenciaFactory extends Factory
{
    protected $model = Ausencia::class;

    public function definition(): array
    {
        $faker      = fake('es_ES');
        $inicio     = $faker->dateTimeBetween('-6 months', 'now');
        $diasDuracion = $faker->numberBetween(1, 14);
        $fin        = (clone $inicio)->modify("+{$diasDuracion} days");
        $estado     = $faker->randomElement([
            EstadoAusencia::PENDIENTE,
            EstadoAusencia::PENDIENTE,
            EstadoAusencia::APROBADA,
            EstadoAusencia::APROBADA,
            EstadoAusencia::APROBADA,
            EstadoAusencia::RECHAZADA,
        ]);

        return [
            'trabajador_id'   => User::factory()->trabajador(),
            'tipo'            => $faker->randomElement(TipoAusencia::cases()),
            'fecha_inicio'    => $inicio->format('Y-m-d'),
            'fecha_fin'       => $fin->format('Y-m-d'),
            'estado'          => $estado,
            'motivo'          => $faker->sentence(10),
            'observaciones'   => $faker->boolean(30) ? $faker->sentence(8) : null,
            'aprobado_por'    => null,
            'aprobado_at'     => null,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => [
            'estado'       => EstadoAusencia::PENDIENTE,
            'aprobado_por' => null,
            'aprobado_at'  => null,
        ]);
    }

    public function aprobada(): static
    {
        return $this->state(fn () => [
            'estado'      => EstadoAusencia::APROBADA,
            'aprobado_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function rechazada(): static
    {
        return $this->state(fn () => [
            'estado'        => EstadoAusencia::RECHAZADA,
            'observaciones' => fake('es_ES')->sentence(12),
            'aprobado_at'   => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
