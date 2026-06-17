<?php

namespace App\Providers;

use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Borrador;
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
use App\Models\Parte;
use App\Models\ParteLineaMaterial;
use App\Models\ParteLineaPersonal;
use App\Models\TarifaCliente;
use App\Observers\AlbaranLineaMaterialObserver;
use App\Observers\AlbaranLineaPersonalObserver;
use App\Observers\AlbaranObserver;
use App\Observers\ClienteObserver;
use App\Observers\ParteLineaMaterialObserver;
use App\Observers\ParteLineaPersonalObserver;
use App\Observers\ParteObserver;
use App\Observers\TarifaClienteObserver;
use App\Observers\UserTasasObserver;
use App\Policies\PartePolicy;
use App\Policies\AlbaranPolicy;
use App\Policies\BorradorPolicy;
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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Telescope: solo en local y solo si el paquete está instalado
        // (en producción se hace `composer install --no-dev` y el paquete no existe).
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        // Forzar que route() / url() respeten APP_URL aunque se acceda desde
        // otro host (p.ej. el panel se navega por localhost pero queremos que
        // los enlaces de firma se generen con la IP/dominio público).
        if ($appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

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
        Gate::policy(Borrador::class, BorradorPolicy::class);
        Gate::policy(Parte::class, PartePolicy::class);

        // Observers de albaranes:
        //   - AlbaranLineaMaterial: ajuste de stock + snapshot del material.
        //   - AlbaranLineaPersonal: snapshot del trabajador (nombre + tasas).
        //   - Albaran (cabecera): snapshot de cliente, proyecto, concepto,
        //     creador y responsable. Solo se (re)escribe el snapshot cuando
        //     cambia la FK correspondiente (regla "isDirty").
        AlbaranLineaMaterial::observe(AlbaranLineaMaterialObserver::class);
        AlbaranLineaPersonal::observe(AlbaranLineaPersonalObserver::class);
        Albaran::observe(AlbaranObserver::class);

        // Cuando un usuario scoped (Jefe de equipo) crea un cliente, lo añade
        // a su lista de clientes gestionados para no perderlo de vista.
        Cliente::observe(ClienteObserver::class);

        // Tarifas v2:
        //   - TarifaCliente: registra cambios de importe en tarifas_historial
        //     con tipo='cliente'.
        //   - User (tasas): registra cambios de cualquier tasa_* en
        //     tarifas_historial con tipo='trabajador'. El atributo correspondiente
        //     se deriva del mapeo_tasa del catálogo atributos_hora.
        TarifaCliente::observe(TarifaClienteObserver::class);
        User::observe(UserTasasObserver::class);

        // Partes (v2):
        //   - Parte: snapshots de cabecera (operario, proyecto, cliente, tipo
        //     de proyecto) + autocódigo PT-YYYY-NNNN + autoflag es_albaran
        //     desde tipo_proyecto.genera_albaran_por_defecto.
        //   - ParteLineaPersonal: snapshots de trabajador, atributo y
        //     económicos (tarifa+tasa+facturación+coste).
        Parte::observe(ParteObserver::class);
        ParteLineaPersonal::observe(ParteLineaPersonalObserver::class);
        ParteLineaMaterial::observe(ParteLineaMaterialObserver::class);

        // Adjuntar IP y navegador a TODA actividad registrada (CRUD, login, etc.)
        // siempre que haya una petición HTTP real. En consola (seeders, comandos)
        // no hay request del usuario, así que se omite.
        Activity::creating(function (Activity $activity): void {
            if (app()->runningInConsole()) {
                return;
            }

            $request    = request();
            $propiedades = collect($activity->properties ?? []);

            if (! $propiedades->has('ip')) {
                $propiedades->put('ip', $request->ip());
            }
            if (! $propiedades->has('user_agent')) {
                $propiedades->put('user_agent', $request->userAgent());
            }

            $activity->properties = $propiedades;
        });
    }
}
