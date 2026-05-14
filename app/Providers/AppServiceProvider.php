<?php

namespace App\Providers;

use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Empresa;
use App\Models\FamiliaMaterial;
use App\Models\Material;
use App\Models\NumeroPedido;
use App\Models\Proyecto;
use App\Models\Role;
use App\Models\TiposProyecto;
use App\Models\User;
use App\Observers\AlbaranLineaMaterialObserver;
use App\Policies\AlbaranPolicy;
use App\Policies\ClientePolicy;
use App\Policies\ConceptoPolicy;
use App\Policies\EmpresaPolicy;
use App\Policies\FamiliaMaterialPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\NumeroPedidoPolicy;
use App\Policies\ProyectoPolicy;
use App\Policies\RolePolicy;
use App\Policies\TiposProyectoPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(TiposProyecto::class, TiposProyectoPolicy::class);
        Gate::policy(Proyecto::class, ProyectoPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(NumeroPedido::class, NumeroPedidoPolicy::class);
        Gate::policy(FamiliaMaterial::class, FamiliaMaterialPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Concepto::class, ConceptoPolicy::class);
        Gate::policy(Empresa::class, EmpresaPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Albaran::class, AlbaranPolicy::class);

        AlbaranLineaMaterial::observe(AlbaranLineaMaterialObserver::class);
    }
}
