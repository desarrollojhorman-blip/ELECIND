<?php

namespace Database\Factories;

use App\Models\TiposProyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TiposProyecto>
 */
class TiposProyectoFactory extends Factory
{
    protected $model = TiposProyecto::class;

    public function definition(): array
    {
        $tipos = [
            'Reforma',
            'Obra nueva',
            'Mantenimiento',
            'Avería',
            'Instalación',
            'Auditoría',
            'Proyecto industrial',
            'Domótica',
        ];

        return [
            'nombre' => fake()->unique()->randomElement($tipos),
            'descripcion' => fake()->boolean(50) ? fake()->sentence(8) : null,
            'activo' => true,
        ];
    }
}
