<?php

namespace Database\Factories;

use App\Models\Concepto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Concepto>
 */
class ConceptoFactory extends Factory
{
    protected $model = Concepto::class;

    public function definition(): array
    {
        $conceptos = [
            'Mano de obra electricista',
            'Mano de obra ayudante',
            'Cuadro eléctrico',
            'Iluminación interior',
            'Iluminación exterior',
            'Tomas de corriente',
            'Cableado general',
            'Mecanismos',
            'Domótica',
            'Telecomunicaciones',
            'Climatización',
            'Sistema fotovoltaico',
            'Puesta a tierra',
            'Protección contra incendios',
            'Mantenimiento preventivo',
            'Reparación de avería',
            'Boletín de instalación',
            'Inspección OCA',
            'Desplazamiento',
            'Dietas',
        ];

        return [
            'nombre' => fake()->unique()->randomElement($conceptos),
            'descripcion' => fake()->boolean(40) ? fake()->sentence(10) : null,
            'activo' => true,
        ];
    }
}
