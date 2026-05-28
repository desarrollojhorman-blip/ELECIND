@props([
    'title' => null,
    'showBack' => false,
    'backRoute' => null,
])

@php
    $user = auth()->user();
    $nombreCompleto = $user ? trim($user->nombre.' '.$user->apellidos) : '';
    $rolPrincipal = $user?->getRoleNames()->first() ?? 'Usuario';
    $logoUrl = \App\Support\Branding::logoUrl();
    $marca = \App\Support\Branding::nombre();
    $logoZoom = \App\Support\Branding::logoZoom();
@endphp

<header class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between gap-2 bg-primary-700 px-3 text-white shadow"
        x-data="{ menuOpen: false }"
        @keydown.escape.window="menuOpen = false">

    {{-- Atrás (opcional) --}}
    <div class="flex w-10 items-center justify-start">
        @if ($showBack)
            <a href="{{ $backRoute ?? route('mobile.dashboard') }}"
               class="inline-flex size-9 items-center justify-center rounded-md transition-colors hover:bg-white/10"
               aria-label="Atrás">
                <x-heroicon-o-arrow-left class="size-5" />
            </a>
        @endif
    </div>

    {{-- Logo / título centrado --}}
    <div class="flex flex-1 items-center justify-center overflow-hidden"
         x-data="{ logoRoto: false }">
        @if ($title)
            <h1 class="truncate text-base font-semibold">{{ $title }}</h1>
        @elseif ($logoUrl)
            <img x-show="! logoRoto" src="{{ $logoUrl }}" alt="{{ $marca }}"
                 style="max-height: calc(2rem * {{ $logoZoom / 100 }});"
                 class="w-auto" x-on:error="logoRoto = true">
            <span x-show="logoRoto" x-cloak class="text-xs font-medium text-white/80">
                Imagen no disponible
            </span>
        @else
            <span class="text-lg font-bold tracking-wide">{{ $marca }}</span>
        @endif
    </div>

    {{-- Menú ⋮ --}}
    <div class="relative flex w-10 items-center justify-end"
         @click.outside="menuOpen = false">
        <button type="button"
                @click="menuOpen = ! menuOpen"
                :class="menuOpen ? 'bg-white/15' : ''"
                class="inline-flex size-9 items-center justify-center rounded-md transition-colors hover:bg-white/10"
                aria-label="Menú">
            <x-heroicon-o-ellipsis-vertical class="size-5" />
        </button>

        {{-- Dropdown del menú --}}
        <div x-show="menuOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute right-0 top-full mt-1 w-64 overflow-hidden rounded-md border border-slate-200 bg-white text-slate-700 shadow-lg ring-1 ring-slate-900/5">

            {{-- Cabecera con usuario --}}
            @auth
                <div class="border-b border-slate-100 px-3 py-2.5">
                    <p class="truncate text-sm font-medium text-slate-900">{{ $nombreCompleto }}</p>
                    <p class="truncate text-xs capitalize text-slate-500">{{ $rolPrincipal }}</p>
                </div>
            @endauth

            {{-- Acciones --}}
            <div class="py-1">
                <a href="{{ route('mobile.perfil') }}"
                   class="flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors hover:bg-slate-50 hover:text-slate-900">
                    <x-heroicon-o-user-circle class="size-4 shrink-0 text-slate-500" />
                    <span class="flex-1 text-left">Mi perfil</span>
                </a>

                <a href="{{ route('mobile.incidencias.index') }}"
                   class="flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors hover:bg-slate-50 hover:text-slate-900">
                    <x-heroicon-o-exclamation-triangle class="size-4 shrink-0 text-amber-500" />
                    <span class="flex-1 text-left">Mis incidencias</span>
                </a>

                <a href="{{ route('mobile.incidencias.nueva') }}"
                   class="flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors hover:bg-slate-50 hover:text-slate-900">
                    <x-heroicon-o-exclamation-circle class="size-4 shrink-0 text-amber-600" />
                    <span class="flex-1 text-left">Nueva incidencia</span>
                </a>

                @if (auth()->user()?->tieneAccesoWeb())
                    <a href="{{ url('/') }}"
                       class="flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors hover:bg-slate-50 hover:text-slate-900">
                        <x-heroicon-o-computer-desktop class="size-4 shrink-0 text-blue-600" />
                        <span class="flex-1 text-left">Cambiar a panel web</span>
                    </a>
                @endif
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
</header>
