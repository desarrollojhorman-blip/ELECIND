<div>
    <x-ui.page-header title="Mi perfil" subtitle="Información de tu cuenta. Para modificar tus datos contacta con el administrador." />

    @php
        $nombreCompleto = trim($user->nombre . ' ' . $user->apellidos);
        $inicial = mb_strtoupper(mb_substr($nombreCompleto, 0, 1));
    @endphp

    <div class="grid gap-5 md:grid-cols-3">

        {{-- Columna izquierda: identidad --}}
        <x-ui.card class="flex flex-col items-center gap-3 py-8 text-center">
            <div class="flex size-20 items-center justify-center rounded-full bg-primary-100 text-3xl font-bold text-primary-700">
                {{ $inicial }}
            </div>
            <div>
                <p class="text-base font-semibold text-slate-800">{{ $nombreCompleto }}</p>
                <p class="mt-0.5 text-sm capitalize text-slate-500">{{ $user->getRoleNames()->first() ?? 'Sin rol' }}</p>
            </div>
            <div class="mt-1 flex flex-wrap justify-center gap-1.5">
                @if ($user->tieneAccesoWeb())
                    <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                        <x-heroicon-o-computer-desktop class="size-3" /> Web
                    </span>
                @endif
                @if ($user->tieneAccesoMovil())
                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                        <x-heroicon-o-device-phone-mobile class="size-3" /> Móvil
                    </span>
                @endif
            </div>
        </x-ui.card>

        {{-- Columna derecha: datos --}}
        <div class="md:col-span-2">
            <x-ui.card padding="p-0">
                <h3 class="border-b border-slate-100 px-5 py-3.5 text-sm font-semibold text-slate-700">Datos de la cuenta</h3>
                <dl class="divide-y divide-slate-100">
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Usuario</dt>
                        <dd class="col-span-1 text-sm text-slate-800 sm:col-span-2">{{ $user->username ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Nombre completo</dt>
                        <dd class="col-span-1 text-sm text-slate-800 sm:col-span-2">{{ $nombreCompleto ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Email</dt>
                        <dd class="col-span-1 text-sm text-slate-800 sm:col-span-2">{{ $user->email ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Teléfono</dt>
                        <dd class="col-span-1 text-sm text-slate-800 sm:col-span-2">{{ $user->telefono ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">DNI / NIF</dt>
                        <dd class="col-span-1 text-sm text-slate-800 sm:col-span-2">{{ $user->dni ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 px-5 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Roles</dt>
                        <dd class="col-span-1 flex flex-wrap gap-1.5 sm:col-span-2">
                            @forelse ($user->getRoleNames() as $rol)
                                <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-0.5 text-xs font-medium capitalize text-primary-700">
                                    {{ $rol }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">Sin rol asignado</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>

    </div>
</div>
