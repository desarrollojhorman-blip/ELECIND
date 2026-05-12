<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ELECIND - Móvil</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-white text-slate-900">
    <main class="mx-auto max-w-md px-4 py-6">
        <h1 class="text-xl font-semibold">Panel móvil ELECIND</h1>
        <p class="mt-2 text-sm text-slate-600">Entrada inicial para rutas móviles.</p>
    </main>
</body>
</html>
