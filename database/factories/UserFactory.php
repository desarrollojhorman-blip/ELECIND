<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'tipo_usuario' => 'interno',
            'numero_empleado' => 'EMP-'.fake()->unique()->numberBetween(1, 999),
            'tasa_hora' => fake()->randomFloat(3, 15, 35),       // 15–35 €/h base
            'tasa_extra' => fake()->randomFloat(3, 18, 40),      // 18–40 €/h extras
            'tasa_festivo' => fake()->randomFloat(3, 25, 50),    // 25–50 €/h festivo
            'activo' => true,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function trabajador(): static
    {
        return $this->state(fn (array $attrs) => [
            'tipo_usuario' => 'interno',
            'cliente_id' => null,
            'dni' => $this->generarDni(),
        ]);
    }

    public function responsableDe(int $clienteId): static
    {
        return $this->state(fn (array $attrs) => [
            'tipo_usuario' => 'externo',
            'cliente_id' => $clienteId,
            // Los externos (responsables de cliente) no tienen tasas — se guardan como 0.
            'tasa_hora' => 0,
            'tasa_extra' => 0,
            'tasa_festivo' => 0,
            'numero_empleado' => null,
        ]);
    }

    public function administrador(): static
    {
        return $this->state(fn (array $attrs) => [
            'tipo_usuario' => 'interno',
            'cliente_id' => null,
        ]);
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attrs) => [
            'tipo_usuario' => 'interno',
            'cliente_id' => null,
        ]);
    }

    private function generarDni(): string
    {
        $numero = fake()->unique()->numberBetween(10_000_000, 99_999_999);
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';

        return $numero.$letras[$numero % 23];
    }
}
