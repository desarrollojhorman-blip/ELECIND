@props(['active' => null])

@php
    $items = [
        [
            'label' => 'Dashboard',
            'icon' => 'heroicon-o-home',
            'route' => 'web.dashboard',
            'key' => 'dashboard',
            'permission' => null,
        ],
        [
            'label' => 'Proyectos',
            'icon' => 'heroicon-o-folder',
            'route' => null,
            'key' => 'proyectos',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Proyectos',
                    'route' => 'proyectos.index',
                    'key' => 'proyectos_lista',
                    'permission' => 'proyectos.ver',
                ],
                [
                    'label' => 'Grupo proyectos',
                    'route' => 'proyectos.grupos',
                    'key' => 'proyectos_grupos',
                    'permission' => 'grupos_proyecto.ver',
                ],
            ],
        ],
        [
            'label' => 'Albaranes',
            'icon' => 'heroicon-o-document-text',
            'route' => null,
            'key' => 'albaranes_grupo',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Borradores',
                    'route' => 'borradores.index',
                    'key' => 'borradores',
                    'permission' => 'borradores.ver_todos',
                ],
                [
                    'label' => 'Albaranes',
                    'route' => 'albaranes.index',
                    'key' => 'albaranes',
                    'permission' => 'albaranes.ver_todos',
                ],
            ],
        ],
        [
            'label' => 'Clientes',
            'icon' => 'heroicon-o-building-office-2',
            'route' => 'clientes.index',
            'key' => 'clientes',
            'permission' => 'clientes.ver',
        ],
        [
            'label' => 'Usuarios',
            'icon' => 'heroicon-o-users',
            'route' => 'usuarios.index',
            'key' => 'usuarios',
            'permission' => 'usuarios.ver_todos',
        ],
        [
            'label' => 'Tarifas',
            'icon' => 'heroicon-o-banknotes',
            'route' => null,
            'key' => 'tarifas',
            'permission' => 'tarifas.ver',
            'children' => [
                [
                    'label' => 'Clientes',
                    'route' => 'tarifas.clientes',
                    'key' => 'tarifas_clientes',
                    'permission' => 'tarifas.ver',
                ],
                [
                    'label' => 'Trabajadores',
                    'route' => 'tarifas.trabajadores',
                    'key' => 'tarifas_trabajadores',
                    'permission' => 'tarifas.ver',
                ],
                [
                    'label' => 'Historial',
                    'route' => 'tarifas.historial',
                    'key' => 'tarifas_historial',
                    'permission' => 'tarifas.historial_ver',
                ],
            ],
        ],
        ...(\App\Support\Modulos::materialesAvanzado() ? [[
            'label' => 'Materiales',
            'icon' => 'heroicon-o-cube',
            'route' => null,
            'key' => 'materiales',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Pedidos',
                    'route' => 'materiales.pedidos',
                    'key' => 'pedidos',
                    'permission' => 'pedidos.ver',
                ],
                [
                    'label' => 'Familias',
                    'route' => 'materiales.familias',
                    'key' => 'familias',
                    'permission' => 'materiales.familias.ver',
                ],
                [
                    'label' => 'Materiales',
                    'route' => 'materiales.index',
                    'key' => 'materiales_lista',
                    'permission' => 'materiales.ver',
                ],
            ],
        ]] : []),
        [
            'label' => 'Conceptos',
            'icon' => 'heroicon-o-tag',
            'route' => 'conceptos.index',
            'key' => 'conceptos',
            'permission' => 'conceptos.ver',
        ],
        [
            'label' => 'Control de Horas',
            'icon' => 'heroicon-o-clock',
            'route' => 'horas.index',
            'key' => 'horas',
            'permission' => null,
        ],
        [
            'label' => 'Ausencias',
            'icon' => 'heroicon-o-calendar-days',
            'route' => 'ausencias.index',
            'key' => 'ausencias',
            'permission' => 'ausencias.ver_todas',
        ],
        [
            'label' => 'Incidencias',
            'icon' => 'heroicon-o-exclamation-circle',
            'route' => 'incidencias.index',
            'key' => 'incidencias',
            'permission' => 'incidencias.ver_todas',
        ],
        [
            'label' => 'Configuración',
            'icon' => 'heroicon-o-cog-6-tooth',
            'route' => null,
            'key' => 'configuracion',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Empresa',
                    'route' => 'configuracion.empresa',
                    'key' => 'empresa',
                    'permission' => 'empresa.ver',
                ],
                [
                    'label' => 'Ajustes',
                    'route' => 'configuracion.ajustes',
                    'key' => 'ajustes',
                    'permission' => 'ajustes.ver',
                ],
                [
                    'label' => 'Roles y permisos',
                    'route' => 'configuracion.roles',
                    'key' => 'roles',
                    'permission' => 'roles.ver',
                ],
                [
                    'label' => 'API',
                    'route' => 'configuracion.api',
                    'key' => 'api',
                    'permission' => 'api.ver',
                ],
                [
                    'label' => 'Logs',
                    'route' => 'configuracion.logs',
                    'key' => 'logs',
                    'permission' => 'logs.ver',
                ],
                [
                    'label' => 'Licencias',
                    'route' => 'configuracion.licencias',
                    'key' => 'licencias',
                    'permission' => 'licencias.ver',
                ],
            ],
        ],
    ];
@endphp

{{--
    En escritorio (≥ md): posición relative, ancho colapsable (w-60 / w-16).
    En móvil (< md):      posición fixed, siempre w-64, entra/sale con translate.
--}}
<aside x-data="{
        open: true,
        drawerOpen: false,
        menuOpen: false,
        expanded: [],
        init() {
            try {
                const storedOpen = localStorage.getItem('sidebar-open');
                const storedExpanded = localStorage.getItem('sidebar-expanded');

                if (storedOpen !== null) {
                    this.open = storedOpen === 'true';
                }

                if (storedExpanded !== null) {
                    const parsedExpanded = JSON.parse(storedExpanded);
                    if (Array.isArray(parsedExpanded)) {
                        this.expanded = parsedExpanded;
                    }
                }
            } catch (error) {
                // Si localStorage no está disponible, mantenemos el estado por defecto.
            }
        },
        persistState() {
            try {
                localStorage.setItem('sidebar-open', String(this.open));
                localStorage.setItem('sidebar-expanded', JSON.stringify(this.expanded));
            } catch (error) {
                // Sin persistencia si el navegador la bloquea.
            }
        },
        isExpanded(key) { return this.expanded.includes(key); },
        toggleExpand(key) {
            this.expanded = this.isExpanded(key)
                ? []
                : [key];
            this.persistState();
        }
       }"
       @drawer:open.window="drawerOpen = true"
       @drawer:close.window="drawerOpen = false"
       @keydown.escape.window="menuOpen = false; $dispatch('drawer:close')"
       :class="[
           drawerOpen ? 'translate-x-0' : '-translate-x-full',
           open ? 'md:w-60 md:translate-x-0' : 'md:w-16 md:translate-x-0',
       ]"
       class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-200 bg-white transition-all duration-200 md:sticky md:top-0 md:h-screen md:inset-auto md:z-auto">

    @php
        $logoUrl = \App\Support\Branding::logoUrl();
        $marca = \App\Support\Branding::nombre();
        $abreviatura = \App\Support\Branding::abreviatura();
        $logoZoom = \App\Support\Branding::logoZoom();
        $logoEsCuadrado = \App\Support\Branding::logoEsCuadrado();
    @endphp

    <div class="flex h-16 items-center justify-between gap-2 border-b border-slate-200 px-3">
        <a href="{{ route('web.dashboard') }}"
           x-data="{ logoRoto: false }"
           class="flex min-w-0 flex-1 items-center justify-center overflow-hidden">
            @if ($logoUrl)
                <img x-show="(open || drawerOpen) && ! logoRoto" x-transition.opacity src="{{ $logoUrl }}"
                     alt="{{ $marca }}"
                     style="max-height: calc(2.25rem * {{ $logoZoom / 100 }});"
                     class="w-auto" x-on:error="logoRoto = true">

                @if ($logoEsCuadrado)
                    <img x-show="! (open || drawerOpen) && ! logoRoto" x-transition.opacity src="{{ $logoUrl }}"
                         alt="{{ $marca }}" class="size-8 object-contain" x-on:error="logoRoto = true">
                @else
                    <span x-show="! (open || drawerOpen)" x-transition.opacity
                          class="text-lg font-bold text-primary-700">
                        {{ $abreviatura }}
                    </span>
                @endif

                <span x-show="(open || drawerOpen) && logoRoto" x-cloak x-transition.opacity
                      class="text-xs font-medium text-slate-500">
                    Imagen no disponible
                </span>
                @if ($logoEsCuadrado)
                    <span x-show="! (open || drawerOpen) && logoRoto" x-cloak x-transition.opacity
                          class="text-xs font-bold text-slate-400">
                        {{ $abreviatura }}
                    </span>
                @endif
            @else
                <span x-show="open || drawerOpen" x-transition.opacity
                      class="text-lg font-bold tracking-wide text-primary-700">
                    {{ $marca }}
                </span>
                <span x-show="! (open || drawerOpen)" x-transition.opacity
                      class="text-lg font-bold text-primary-700">
                    {{ $abreviatura }}
                </span>
            @endif
        </a>

        {{-- Cerrar drawer (solo móvil) --}}
        <button type="button"
                @click="$dispatch('drawer:close')"
                class="shrink-0 rounded-md p-1.5 text-slate-500 hover:bg-slate-100 md:hidden">
            <x-heroicon-o-x-mark class="size-5" />
        </button>

        {{-- Colapsar/expandir sidebar (solo escritorio) --}}
        <button type="button"
                @click="open = ! open"
                class="hidden shrink-0 rounded-md p-1.5 text-slate-500 hover:bg-slate-100 md:block"
                :title="open ? 'Colapsar menú' : 'Expandir menú'">
            <x-heroicon-o-bars-3 class="size-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-2 py-3">
        <ul class="space-y-0.5">
            @foreach ($items as $item)
                @if ($item['permission'] && ! auth()->user()?->can($item['permission']))
                    @continue
                @endif

                @php
                    $childrenVisibles = collect($item['children'] ?? [])
                        ->filter(fn ($c) => empty($c['permission']) || auth()->user()?->can($c['permission']))
                        ->values()
                        ->all();

                    if (! empty($item['children']) && $item['route'] === null && count($childrenVisibles) === 0) {
                        continue;
                    }

                    $hasChildren = count($childrenVisibles) > 0;
                    $isActive = $active === $item['key'] || collect($childrenVisibles)->pluck('key')->contains($active);
                    $disabled = $item['route'] === null && ! $hasChildren;
                    $href = $item['route'] ? route($item['route']) : '#';
                    $isToggle = $hasChildren && $item['route'] === null;
                    $itemClasses = [
                        'group flex w-full items-center gap-3 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
                        'bg-accent-100 text-primary-700' => $isActive,
                        'text-slate-700 hover:bg-slate-50 hover:text-slate-900' => ! $isActive && ! $disabled,
                        'text-slate-400 cursor-not-allowed' => $disabled,
                    ];
                @endphp

                <li>
                    @if ($isToggle)
                        <button type="button"
                                @click="(open || drawerOpen) ? toggleExpand('{{ $item['key'] }}') : (open = true, persistState(), expanded = isExpanded('{{ $item['key'] }}') ? expanded : [...expanded, '{{ $item['key'] }}'])"
                                @class($itemClasses)>
                            <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" />
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 truncate text-left">
                                {{ $item['label'] }}
                            </span>
                            <x-heroicon-m-chevron-right
                                x-show="open || drawerOpen"
                                x-bind:class="(isExpanded('{{ $item['key'] }}') || {{ $isActive ? 'true' : 'false' }}) ? 'rotate-90' : ''"
                                class="size-4 shrink-0 text-slate-400 transition-transform" />
                        </button>
                    @else
                        <a href="{{ $href }}"
                           @if ($disabled) aria-disabled="true" @endif
                           @class($itemClasses)>
                            <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" />
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 truncate text-left">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @endif

                    @if ($hasChildren)
                        <ul x-show="(open || drawerOpen) && (isExpanded('{{ $item['key'] }}') || {{ $isActive ? 'true' : 'false' }})"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="mt-0.5 ml-7 space-y-0.5 border-l border-slate-200 pl-2">
                            @foreach ($childrenVisibles as $child)
                                @php
                                    $childActive = $active === $child['key'];
                                    $childHref = $child['route'] ? route($child['route']) : '#';
                                @endphp
                                <li>
                                    <a href="{{ $childHref }}"
                                       @class([
                                           'block rounded-md px-2.5 py-1.5 text-sm transition-colors',
                                           'bg-primary-50 text-primary-700 font-medium' => $childActive,
                                           'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $childActive,
                                       ])>
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>

    @auth
        @php
            $user = auth()->user();
            $nombreCompleto = trim($user->nombre.' '.$user->apellidos);
            $inicial = mb_strtoupper(mb_substr($nombreCompleto, 0, 1));
            $rolPrincipal = $user->getRoleNames()->first() ?? 'Sin rol';
        @endphp
        <div class="border-t border-slate-200">

            <div x-show="menuOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="border-b border-slate-100">

                <div class="px-2 py-1">
                    <a href="{{ route('perfil.mi-perfil') }}"
                       class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                        <x-heroicon-o-user-circle class="size-4 shrink-0" />
                        <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 text-left">Mi perfil</span>
                    </a>

                    @if ($user->tieneAccesoMovil())
                        <a href="{{ route('mobile.dashboard') }}"
                           class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <x-heroicon-o-device-phone-mobile class="size-4 shrink-0" />
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 text-left">Versión móvil</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                            <x-heroicon-o-arrow-right-on-rectangle class="size-4 shrink-0" />
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 text-left">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-2">
                <button type="button"
                        @click="menuOpen = ! menuOpen"
                        :class="menuOpen ? 'bg-slate-100' : ''"
                        class="flex w-full items-center gap-2.5 rounded-md px-1.5 py-1.5 transition-colors hover:bg-slate-100">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                        {{ $inicial }}
                    </div>
                    <div x-show="open || drawerOpen" x-transition.opacity class="min-w-0 flex-1 text-left">
                        <p class="truncate text-sm font-medium text-slate-800">{{ $nombreCompleto }}</p>
                        <p class="truncate text-xs capitalize text-slate-500">{{ $rolPrincipal }}</p>
                    </div>
                    <x-heroicon-m-chevron-up
                        x-show="open || drawerOpen"
                        x-bind:class="menuOpen ? '' : 'rotate-180'"
                        class="size-4 shrink-0 text-slate-400 transition-transform" />
                </button>
            </div>

        </div>
    @endauth
</aside>
