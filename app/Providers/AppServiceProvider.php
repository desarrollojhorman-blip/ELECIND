<?php

namespace App\Providers;

use App\Models\EmpresasCliente;
use App\Models\Material;
use App\Models\MaterialLote;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Policies\EmpresasClientePolicy;
use App\Policies\MaterialLotePolicy;
use App\Policies\MaterialPolicy;
use App\Policies\ProyectoPolicy;
use App\Policies\TiposProyectoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(EmpresasCliente::class, EmpresasClientePolicy::class);
        Gate::policy(TiposProyecto::class, TiposProyectoPolicy::class);
        Gate::policy(Proyecto::class, ProyectoPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(MaterialLote::class, MaterialLotePolicy::class);
    }
}
