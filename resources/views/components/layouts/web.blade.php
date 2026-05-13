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
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="flex min-h-screen">
        <x-ui.sidebar :active="$active" />

        <main class="flex min-w-0 flex-1 flex-col overflow-y-auto px-4 py-5 lg:px-6 lg:py-6">
            <x-ui.flash />
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
