<?php

namespace Database\Factories;

use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Incidencia> */
class IncidenciaFactory extends Factory
{
    protected $model = Incidencia::class;

    private const TITULOS = [
        'Avería en equipo de medición',
        'Fallo en cuadro eléctrico',
        'Cortocircuito en línea de distribución',
        'Problema con herramienta de corte',
        'Incidencia de seguridad en obra',
        'Material defectuoso recibido',
        'Rotura de cable de alimentación',
        'Fallo en instalación de luminarias',
        'Equipo sin calibrar detectado',
        'Acceso no autorizado a zona de trabajo',
        'Falta de EPI en puesto de trabajo',
        'Interruptor diferencial disparado repetidamente',
        'Problema con grúa de carga',
        'Retraso en entrega de materiales',
        'Avería en taladro percutor',
    ];

    public function definition(): array
    {
        $faker    = fake('es_ES');
        $estado   = $faker->randomElement([
            EstadoIncidencia::PENDIENTE,
            EstadoIncidencia::PENDIENTE,
            EstadoIncidencia::EN_PROCESO,
            EstadoIncidencia::RESUELTA,
            EstadoIncidencia::RESUELTA,
            EstadoIncidencia::CERRADA,
        ]);
        $resuelta = in_array($estado, [EstadoIncidencia::RESUELTA, EstadoIncidencia::CERRADA]);

        return [
            'trabajador_id'  => User::factory()->trabajador(),
            'tipo'           => $faker->randomElement(TipoIncidencia::cases()),
            'prioridad'      => $faker->randomElement([
                PrioridadIncidencia::BAJA,
                PrioridadIncidencia::MEDIA,
                PrioridadIncidencia::MEDIA,
                PrioridadIncidencia::ALTA,
                PrioridadIncidencia::URGENTE,
            ]),
            'titulo'         => $faker->randomElement(self::TITULOS),
            'descripcion'    => $faker->paragraph(3),
            'estado'         => $estado,
            'resolucion'     => $resuelta ? $faker->paragraph(2) : null,
            'resuelto_por'   => null,
            'resuelto_at'    => $resuelta ? now()->subDays($faker->numberBetween(1, 60)) : null,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => [
            'estado'       => EstadoIncidencia::PENDIENTE,
            'resolucion'   => null,
            'resuelto_por' => null,
            'resuelto_at'  => null,
        ]);
    }

    public function enProceso(): static
    {
        return $this->state(fn () => [
            'estado'       => EstadoIncidencia::EN_PROCESO,
            'resolucion'   => null,
            'resuelto_por' => null,
            'resuelto_at'  => null,
        ]);
    }

    public function resuelta(): static
    {
        return $this->state(fn () => [
            'estado'      => EstadoIncidencia::RESUELTA,
            'resolucion'  => fake('es_ES')->paragraph(2),
            'resuelto_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ]);
    }
}
