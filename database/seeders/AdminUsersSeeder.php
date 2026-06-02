<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (App::environment(['local', 'development', 'testing'])) {
            $superadmin = User::updateOrCreate(
                ['username' => 'superadmin'],
                [
                    'nombre' => 'Super',
                    'apellidos' => 'Admin',
                    'email' => 'superadmin@elecind.local',
                    'tipo_usuario' => 'interno',
                    'activo' => true,
                    'password' => '123456',
                ]
            );

            $admin = User::updateOrCreate(
                ['username' => 'admin'],
                [
                    'nombre' => 'Admin',
                    'apellidos' => 'Elecind',
                    'email' => 'admin@elecind.local',
                    'tipo_usuario' => 'interno',
                    'activo' => true,
                    'password' => '123456',
                ]
            );

            $trabajadorDemo = User::updateOrCreate(
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
                    'password' => '123456',
                ]
            );

            $superadmin->assignRole('superadmin');
            $admin->assignRole('administrador');
            $trabajadorDemo->assignRole('trabajador');

            return;
        }

        $superadmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'nombre' => 'Super',
                'apellidos' => 'Admin',
                'email' => 'superadmin@elecind.local',
                'tipo_usuario' => 'interno',
                'activo' => true,
                'password' => 'EPZCf2fq7F(zxi8x',
            ]
        );

        $superadmin->assignRole('superadmin');
    }
}
