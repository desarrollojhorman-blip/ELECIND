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
            'label' => 'Control de Horas',
            'icon' => 'heroicon-o-clock',
            'route' => null,
            'key' => 'horas',
            'permission' => null,
        ],
        [
            'label' => 'Proyectos',
            'icon' => 'heroicon-o-folder',
            'route' => 'proyectos.index',
            'key' => 'proyectos',
            'permission' => 'proyectos.ver',
        ],
        [
            'label' => 'Albaranes',
            'icon' => 'heroicon-o-document-text',
            'route' => null,
            'key' => 'albaranes',
            'permission' => null,
        ],
        [
            'label' => 'Materiales',
            'icon' => 'heroicon-o-cube',
            'route' => 'materiales.index',
            'key' => 'materiales',
            'permission' => 'materiales.ver',
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
            'label' => 'Ausencias',
            'icon' => 'heroicon-o-calendar-days',
            'route' => null,
            'key' => 'ausencias',
            'permission' => 'ausencias.ver_todas',
        ],
        [
            'label' => 'Incidencias',
            'icon' => 'heroicon-o-exclamation-circle',
            'route' => null,
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
                    'permission' => 'configuracion.empresa',
                ],
                [
                    'label' => 'Roles y permisos',
                    'route' => 'configuracion.roles',
                    'key' => 'roles',
                    'permission' => 'roles.gestionar',
                ],
                [
                    'label' => 'Conceptos',
                    'route' => 'conceptos.index',
                    'key' => 'conceptos',
                    'permission' => 'conceptos.ver',
                ],
            ],
        ],
    ];
@endphp

<aside x-data="{
        open: $persist(true).as('sidebar-open'),
        menuOpen: false,
        expanded: $persist([]).as('sidebar-expanded'),
        isExpanded(key) { return this.expanded.includes(key); },
        toggleExpand(key) {
            this.expanded = this.isExpanded(key)
                ? this.expanded.filter(i => i !== key)
                : [...this.expanded, key];
        }
       }"
       @keydown.escape.window="menuOpen = false"
       :class="open ? 'w-60' : 'w-16'"
       class="hidden shrink-0 flex-col border-r border-slate-200 bg-white transition-all duration-200 md:flex">
    {{-- Brand --}}
    @php
        $logoUrl = \App\Support\Branding::logoUrl();
        $marca = \App\Support\Branding::nombre();
        $abreviatura = \App\Support\Branding::abreviatura();
    @endphp
    <div class="flex h-16 items-center justify-between gap-2 border-b border-slate-200 px-3">
        <a href="{{ route('web.dashboard') }}" class="flex min-w-0 flex-1 items-center justify-center overflow-hidden">
            @if ($logoUrl)
                {{-- Logo expandido --}}
                <img x-show="open" x-transition.opacity src="{{ $logoUrl }}" alt="{{ $marca }}" class="max-h-9 w-auto">
                {{-- Logo colapsado: misma imagen reducida --}}
                <img x-show="! open" x-transition.opacity src="{{ $logoUrl }}" alt="{{ $marca }}" class="size-8 object-contain">
            @else
                {{-- Sin logo configurado: texto placeholder --}}
                <span x-show="open" x-transition.opacity
                      class="text-lg font-bold tracking-wide text-primary-700">
                    {{ $marca }}
                </span>
                <span x-show="! open" x-transition.opacity
                      class="text-lg font-bold text-primary-700">
                    {{ $abreviatura }}
                </span>
            @endif
        </a>
        <button type="button"
                @click="open = ! open"
                class="shrink-0 rounded-md p-1.5 text-slate-500 hover:bg-slate-100"
                :title="open ? 'Colapsar menú' : 'Expandir menú'">
            <x-heroicon-o-bars-3 class="size-5" />
        </button>
    </div>

    {{-- Items --}}
    <nav class="flex-1 overflow-y-auto px-2 py-3">
        <ul class="space-y-0.5">
            @foreach ($items as $item)
                @if ($item['permission'] && ! auth()->user()?->can($item['permission']))
                    @continue
                @endif

                @php
                    // Filtrar children por permission individual antes de pintar.
                    $childrenVisibles = collect($item['children'] ?? [])
                        ->filter(fn ($c) => empty($c['permission']) || auth()->user()?->can($c['permission']))
                        ->values()
                        ->all();
                @endphp

                {{-- Si el item es un contenedor (sin route propia) y ningún hijo es visible, saltar. --}}
                @if (! empty($item['children']) && $item['route'] === null && count($childrenVisibles) === 0)
                    @continue
                @endif

                @php
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
                        {{-- Item con submenú: botón que expande/colapsa --}}
                        <button type="button"
                                @click="open ? toggleExpand('{{ $item['key'] }}') : (open = true, expanded = isExpanded('{{ $item['key'] }}') ? expanded : [...expanded, '{{ $item['key'] }}'])"
                                @class($itemClasses)>
                            <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" />
                            <span x-show="open" x-transition.opacity class="flex-1 truncate text-left">
                                {{ $item['label'] }}
                            </span>
                            <x-heroicon-m-chevron-right
                                x-show="open"
                                x-bind:class="(isExpanded('{{ $item['key'] }}') || {{ $isActive ? 'true' : 'false' }}) ? 'rotate-90' : ''"
                                class="size-4 shrink-0 text-slate-400 transition-transform" />
                        </button>
                    @else
                        {{-- Item normal: enlace --}}
                        <a href="{{ $href }}"
                           @if ($disabled) aria-disabled="true" @endif
                           @class($itemClasses)>
                            <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" />
                            <span x-show="open" x-transition.opacity class="flex-1 truncate text-left">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @endif

                    {{-- Submenú: visible si está activo (estás dentro) o si lo expandiste manualmente --}}
                    @if ($hasChildren)
                        <ul x-show="open && (isExpanded('{{ $item['key'] }}') || {{ $isActive ? 'true' : 'false' }})"
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

    {{-- Footer: avatar como dropdown que contiene cerrar sesión --}}
    @auth
        @php
            $user = auth()->user();
            $nombreCompleto = trim($user->nombre.' '.$user->apellidos);
            $inicial = mb_strtoupper(mb_substr($nombreCompleto, 0, 1));
            $rolPrincipal = $user->getRoleNames()->first() ?? 'Sin rol';
        @endphp
        <div class="relative border-t border-slate-200 p-2"
             @click.outside="menuOpen = false">
            {{-- Botón disparador --}}
            <button type="button"
                    @click="menuOpen = ! menuOpen"
                    :class="menuOpen ? 'bg-slate-100' : ''"
                    class="flex w-full items-center gap-2.5 rounded-md px-1.5 py-1.5 transition-colors hover:bg-slate-100">
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                    {{ $inicial }}
                </div>
                <div x-show="open" x-transition.opacity class="min-w-0 flex-1 text-left">
                    <p class="truncate text-sm font-medium text-slate-800">
                        {{ $nombreCompleto }}
                    </p>
                    <p class="truncate text-xs capitalize text-slate-500">
                        {{ $rolPrincipal }}
                    </p>
                </div>
                <x-heroicon-m-chevron-up
                    x-show="open"
                    x-transition.opacity
                    x-bind:class="menuOpen ? 'rotate-180' : ''"
                    class="size-4 shrink-0 text-slate-400 transition-transform" />
            </button>

            {{-- Menú flotante --}}
            <div x-show="menuOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 :class="open ? 'bottom-full left-2 right-2 mb-1' : 'bottom-2 left-full ml-1 w-56'"
                 class="absolute z-50 overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg ring-1 ring-slate-900/5">
                <div class="border-b border-slate-100 px-3 py-2.5">
                    <p class="truncate text-sm font-medium text-slate-900">{{ $nombreCompleto }}</p>
                    <p class="truncate text-xs capitalize text-slate-500">{{ $rolPrincipal }}</p>
                </div>
                <div class="py-1">
                    <button type="button"
                            disabled
                            title="Próximamente"
                            class="flex w-full cursor-not-allowed items-center gap-2.5 px-3 py-2 text-sm text-slate-400">
                        <x-heroicon-o-user-circle class="size-4 shrink-0" />
                        <span class="flex-1 text-left">Mi perfil</span>
                        <span class="text-[10px] uppercase tracking-wide text-slate-400">Pronto</span>
                    </button>
                </div>
                <div class="border-t border-slate-100 py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                            <x-heroicon-o-arrow-right-on-rectangle class="size-4 shrink-0" />
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endauth
</aside>
