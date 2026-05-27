@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · '.\App\Support\Branding::nombre() : \App\Support\Branding::nombre() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-3xl items-center px-4 py-3">
            <span class="text-base font-bold" style="color: {{ \App\Support\Branding::colorPrimario() }}">
                {{ \App\Support\Branding::nombre() }}
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
