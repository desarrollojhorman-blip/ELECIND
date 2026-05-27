<div class="space-y-6">

    <div>
        <h2 class="text-xl font-semibold text-slate-900">Licencias</h2>
        <p class="text-sm text-slate-500">Información sobre la licencia del sistema.</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white px-8 py-8 shadow-sm space-y-8">

        {{-- Datos empresa --}}
        <div class="space-y-1.5">
            <p class="font-bold text-slate-900 mb-3">DATOS EMPRESA</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">NIF:</span> {{ $empresa->cif ?? '—' }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">RAZÓN SOCIAL:</span> {{ $empresa->nombre ?? '—' }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">DIRECCIÓN:</span> {{ $empresa->direccion ?? '—' }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">CP:</span> {{ $empresa->codigo_postal ?? '—' }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">POBLACIÓN:</span> {{ collect([$empresa->poblacion, $empresa->provincia])->filter()->implode(', ') ?: '—' }}</p>
            @if ($empresa->telefono)
                <p class="text-sm text-slate-700"><span class="font-semibold">TELÉFONO:</span> {{ $empresa->telefono }}</p>
            @endif
            @if ($empresa->email_contacto)
                <p class="text-sm text-slate-700"><span class="font-semibold">EMAIL:</span> {{ $empresa->email_contacto }}</p>
            @endif
        </div>

        {{-- Producto licenciado --}}
        <div class="space-y-1.5">
            <p class="font-bold text-slate-900 mb-3">PRODUCTO LICENCIADO</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">NOMBRE PRODUCTO:</span> {{ $licencia['producto'] }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">FABRICANTE:</span> {{ $licencia['fabricante'] }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">FECHA DE LICENCIA:</span> {{ $licencia['emision']->format('d/m/Y') }} – {{ $licencia['caducidad']->format('d/m/Y') }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">VERSIÓN DEL PRODUCTO:</span> {{ $licencia['version'] }}</p>
        </div>

        {{-- Usuarios y módulos --}}
        <div class="space-y-1.5">
            <p class="font-bold text-slate-900 mb-3">USO Y MÓDULOS</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">USUARIOS ACTIVOS:</span> {{ $usuariosActivos }} de {{ $licencia['max_usuarios'] }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">PLAN:</span> {{ $licencia['plan'] }}</p>
            <p class="text-sm text-slate-700"><span class="font-semibold">MÓDULOS:</span> {{ collect($modulos)->where('activo', true)->pluck('nombre')->implode(', ') }}</p>
        </div>

    </div>

</div>
