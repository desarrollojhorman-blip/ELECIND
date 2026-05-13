<?php

namespace Database\Factories;

use App\Models\EmpresasCliente;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proyecto>
 */
class ProyectoFactory extends Factory
{
    protected $model = Proyecto::class;

    public function definition(): array
    {
        $faker = fake('es_ES');
        $fechaInicio = $faker->dateTimeBetween('-8 months', '+1 month');
        $fechaFin = $faker->boolean(60)
            ? $faker->dateTimeBetween($fechaInicio, '+6 months')->format('Y-m-d')
            : null;

        return [
            'empresa_cliente_id' => EmpresasCliente::factory(),
            'tipo_proyecto_id' => TiposProyecto::factory(),
            'nombre' => ucfirst($faker->words(3, true)),
            'codigo' => strtoupper($faker->unique()->bothify('PRY-####-??')),
            'descripcion' => $faker->boolean(60) ? $faker->paragraph(2) : null,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin,
            'estado' => $faker->randomElement(['borrador', 'activo', 'activo', 'activo', 'cerrado']),
            'responsable_principal_id' => null,
        ];
    }

    public function activo(): static
    {
        return $this->state(fn () => ['estado' => 'activo']);
    }

    public function cerrado(): static
    {
        return $this->state(fn () => ['estado' => 'cerrado']);
    }
}
