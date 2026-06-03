@props([
    'title' => null,
    'active' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · '.\App\Support\Branding::nombre() : \App\Support\Branding::nombre() }}</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    {{-- Override de tokens visuales con la configuración guardada --}}
    <style>
        :root {
            --c-primary-700: {{ \App\Support\Branding::colorPrimario() }};
            --c-accent-100: {{ \App\Support\Branding::colorSecundario() }};
            --c-table-header-text: {{ \App\Support\Branding::colorTextoEncabezado() }};
        }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased">

    {{-- Barra superior solo en móvil --}}
    <header class="flex h-14 shrink-0 items-center border-b border-slate-200 bg-white px-4 md:hidden">
        <button type="button"
                x-data
                @click="$dispatch('drawer:open')"
                class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100">
            <x-heroicon-o-bars-3 class="size-6" />
        </button>
    </header>

    {{-- Overlay oscuro al abrir el drawer --}}
    <div x-data="{ open: false }"
         @drawer:open.window="open = true"
         @drawer:close.window="open = false"
         x-show="open"
         x-cloak
         @click="$dispatch('drawer:close')"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-30 bg-black/40 md:hidden">
    </div>

    <div class="flex flex-1">
        <x-ui.sidebar :active="$active" />

        <main class="flex min-w-0 flex-1 flex-col px-4 py-5 lg:px-6 lg:py-6">
            <x-ui.flash />
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
