<div>
    <div class="px-4 py-3 space-y-3">
        @php
            $nombreCompleto = trim($user->nombre . ' ' . $user->apellidos);
            $inicial = mb_strtoupper(mb_substr($nombreCompleto, 0, 1));
        @endphp

        {{-- Avatar + nombre --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-100 text-2xl font-bold text-primary-700">
                {{ $inicial }}
            </div>
            <div class="min-w-0">
                <p class="truncate font-semibold text-slate-800">{{ $nombreCompleto ?: '—' }}</p>
                <p class="truncate text-sm capitalize text-slate-500">{{ $user->getRoleNames()->first() ?? 'Sin rol' }}</p>
                <div class="mt-1.5 flex flex-wrap gap-1">
                    @if ($user->tieneAccesoWeb())
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-1.5 py-0.5 text-xs font-medium text-blue-700">Web</span>
                    @endif
                    @if ($user->tieneAccesoMovil())
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-xs font-medium text-emerald-700">Móvil</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Datos --}}
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
            <p class="border-b border-slate-100 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">Datos de la cuenta</p>
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex justify-between gap-3 px-4 py-3">
                    <dt class="text-slate-500">Usuario</dt>
                    <dd class="font-medium text-slate-800">{{ $user->username ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 px-4 py-3">
                    <dt class="text-slate-500">Nombre</dt>
                    <dd class="font-medium text-slate-800 text-right">{{ $nombreCompleto ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 px-4 py-3">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium text-slate-800 text-right">{{ $user->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 px-4 py-3">
                    <dt class="text-slate-500">Teléfono</dt>
                    <dd class="font-medium text-slate-800">{{ $user->telefono ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 px-4 py-3">
                    <dt class="text-slate-500">DNI / NIF</dt>
                    <dd class="font-medium text-slate-800">{{ $user->dni ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <p class="text-center text-xs text-slate-400">Para modificar tus datos contacta con el administrador.</p>
    </div>
</div>
