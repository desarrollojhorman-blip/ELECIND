<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

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
            'activo' => true,
            'password' => 'password',
        ]);
        $user->assignRole('superadmin');

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
