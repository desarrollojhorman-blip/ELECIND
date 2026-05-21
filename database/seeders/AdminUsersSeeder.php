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

        $trabajadorDemo = User::firstOrCreate(
            ['username' => 'trabajador'],
            [
                'nombre' => 'Trabajador',
                'apellidos' => 'Demo',
                'email' => 'trabajador@elecind.local',
                'tipo_usuario' => 'interno',
                'numero_empleado' => 'EMP-001',
                'tasa_hora' => 22.500,
                'tasa_extra' => 28.000,
                'tasa_festivo' => 35.000,
                'activo' => true,
                'password' => 'password',
            ]
        );

        $superadmin->assignRole('superadmin');
        $admin->assignRole('administrador');
        $trabajadorDemo->assignRole('trabajador');
    }
}
