@props([
    'title' => null,
    'showHeader' => true,
    'showBack' => false,
    'backRoute' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ \App\Support\Branding::colorPrimario() }}">
    <title>{{ $title ? $title.' · '.\App\Support\Branding::nombre() : \App\Support\Branding::nombre() }}</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="mx-auto flex min-h-screen max-w-[480px] flex-col bg-white shadow-sm sm:my-4 sm:min-h-[calc(100vh-2rem)] sm:rounded-lg">
        @if ($showHeader)
            <x-mobile.header
                :title="$title"
                :show-back="$showBack"
                :back-route="$backRoute" />
        @endif

        <main class="flex-1 overflow-y-auto">
            <x-ui.flash />
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
