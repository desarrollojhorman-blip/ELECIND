<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        $faker = fake('es_ES');
        $nombre = $faker->company();

        return [
            'codigo_cliente' => (string) $faker->unique()->numberBetween(1, 999_999),
            'nombre' => $nombre,
            'nombre_comercial' => $faker->boolean(40) ? $faker->companySuffix().' '.$faker->lastName() : null,
            'cif' => $this->generarCif(),
            'direccion' => $faker->streetAddress(),
            'codigo_postal' => $faker->postcode(),
            'poblacion' => $faker->city(),
            'provincia' => $faker->state(),
            'telefono' => $faker->phoneNumber(),
            'email' => $faker->unique()->companyEmail(),
            'activo' => $faker->boolean(90),
            'observaciones' => $faker->boolean(25) ? $faker->sentence(8) : null,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }

    private function generarCif(): string
    {
        $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $letra = $letras[array_rand($letras)];
        $numero = str_pad((string) fake()->unique()->numberBetween(10_000_000, 99_999_999), 8, '0', STR_PAD_LEFT);

        return $letra.$numero;
    }
}
