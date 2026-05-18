<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Support\Branding::nombre() }} · Iniciar sesión</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --c-primary-700: {{ \App\Support\Branding::colorPrimario() }};
            --c-accent-100: {{ \App\Support\Branding::colorSecundario() }};
            --c-table-header-text: {{ \App\Support\Branding::colorTextoEncabezado() }};
        }
    </style>
</head>
<body class="min-h-screen bg-slate-800 antialiased">

    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">

        {{-- Tarjeta central --}}
        <div class="w-full max-w-sm">

            {{-- Logo / nombre --}}
            <div class="mb-8 flex flex-col items-center gap-3">
                @php $logoUrl = \App\Support\Branding::logoUrl(); @endphp
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}"
                         alt="{{ \App\Support\Branding::nombre() }}"
                         class="h-14 w-auto object-contain"
                         style="max-height: calc(3.5rem * {{ \App\Support\Branding::logoZoom() / 100 }});">
                @else
                    <div class="flex size-14 items-center justify-center rounded-xl text-white text-2xl font-bold"
                         style="background-color: var(--c-primary-700);">
                        {{ \App\Support\Branding::abreviatura() }}
                    </div>
                @endif
                <span class="text-xl font-semibold text-white tracking-tight">
                    {{ \App\Support\Branding::nombre() }}
                </span>
            </div>

            {{-- Card --}}
            <div class="rounded-2xl bg-white px-8 py-8 shadow-xl">

                <h1 class="mb-6 text-center text-lg font-semibold text-slate-800">
                    Iniciar sesión
                </h1>

                @if ($errors->any())
                    <div class="mb-4 flex items-start gap-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        <x-heroicon-o-exclamation-circle class="mt-0.5 size-4 shrink-0" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ mostrarPassword: false }">
                    @csrf

                    {{-- Usuario --}}
                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Usuario
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <x-heroicon-o-user class="size-4" />
                            </span>
                            <input id="username"
                                   name="username"
                                   type="text"
                                   autocomplete="username"
                                   required
                                   autofocus
                                   value="{{ old('username') }}"
                                   placeholder="Nombre de usuario"
                                   class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 focus:border-primary-700 focus:outline-none focus:ring-1 focus:ring-primary-700 @error('username') border-red-400 @enderror" />
                        </div>
                        @error('username')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Contraseña
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <x-heroicon-o-lock-closed class="size-4" />
                            </span>
                            <input id="password"
                                   name="password"
                                   :type="mostrarPassword ? 'text' : 'password'"
                                   autocomplete="current-password"
                                   required
                                   placeholder="Contraseña"
                                   class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-primary-700 focus:outline-none focus:ring-1 focus:ring-primary-700 @error('password') border-red-400 @enderror" />
                            <button type="button"
                                    x-on:click="mostrarPassword = !mostrarPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                                    :title="mostrarPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <x-heroicon-o-eye x-show="!mostrarPassword" class="size-4" />
                                <x-heroicon-o-eye-slash x-show="mostrarPassword" class="size-4" x-cloak />
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recuérdame --}}
                    <div class="flex items-center gap-2">
                        <input id="remember"
                               name="remember"
                               type="checkbox"
                               class="size-4 rounded border-slate-300 text-primary-700 focus:ring-primary-700" />
                        <label for="remember" class="text-sm text-slate-600">
                            Recuérdame
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 px-4 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="background-color: var(--c-primary-700);">
                        <x-heroicon-o-arrow-right-on-rectangle class="size-4" />
                        Entrar
                    </button>

                </form>
            </div>

            {{-- Pie versión --}}
            <p class="mt-6 text-center text-xs text-slate-500">
                ENIA &middot; v{{ config('app.version', '1.0') }}
            </p>

        </div>
    </div>

</body>
</html>
