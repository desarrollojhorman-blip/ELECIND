<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // En producción sembramos solo base mínima; los datos demo se reservan para local/testing.
        $seeders = [
            RolesAndPermissionsSeeder::class,
        ];

        if (App::environment(['local', 'development', 'testing'])) {
            $seeders = array_merge($seeders, [
                AdminUsersSeeder::class,
                Fase1DemoSeeder::class,
                Fase2DemoSeeder::class,
                Fase3DemoSeeder::class,
            ]);
        }

        $this->call($seeders);
    }
}
