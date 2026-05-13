<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'nombre' => 'Super',
                'apellidos' => 'Admin',
                'email' => 'superadmin@elecind.local',
                'tipo_usuario' => 'interno',
                'activo' => true,
                'password' => 'password',
            ]
        );

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nombre' => 'Admin',
                'apellidos' => 'Elecind',
                'email' => 'admin@elecind.local',
                'tipo_usuario' => 'interno',
                'activo' => true,
                'password' => 'password',
            ]
        );

        $superadmin->assignRole('superadmin');
        $admin->assignRole('administrador');
    }
}
