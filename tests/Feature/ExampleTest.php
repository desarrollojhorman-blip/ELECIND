<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'nombre' => 'Test',
            'apellidos' => 'User',
            'email' => 'test@example.com',
            'tipo_usuario' => 'interno',
            'acceso' => 'ambos',
            'activo' => true,
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
