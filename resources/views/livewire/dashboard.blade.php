<div class="space-y-6">

    {{-- Cabecera --}}
    <div>
        <h2 class="text-xl font-semibold text-slate-900">
            Hola, {{ auth()->user()?->nombre }} 👋
        </h2>
        <p class="text-sm text-slate-500">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">

        @can('albaranes.ver_todos')
        <a href="{{ route('albaranes.index') }}"
           class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-amber-300 hover:shadow-md">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-slate-500">Pdte. firma</p>
                    <p class="mt-1 text-3xl font-bold text-amber-500">{{ $kpis['pendientes_firma'] }}</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-1.5 text-amber-500">
                    <x-heroicon-o-clock class="size-5" />
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400 group-hover:text-amber-500">Ver albaranes →</p>
        </a>
        @endcan

        @can('albaranes.ver_todos')
        <a href="{{ route('albaranes.index') }}"
           class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-green-300 hover:shadow-md">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-slate-500">Firmados</p>
                    <p class="mt-1 text-3xl font-bold text-green-600">{{ $kpis['firmados'] }}</p>
                </div>
                <div class="rounded-lg bg-green-50 p-1.5 text-green-600">
                    <x-heroicon-o-check-badge class="size-5" />
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400 group-hover:text-green-600">Ver albaranes →</p>
        </a>
        @endcan

        @can('borradores.ver_todos')
        <a href="{{ route('borradores.index') }}"
           class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-slate-500">Borradores</p>
                    <p class="mt-1 text-3xl font-bold text-blue-600">{{ $kpis['borradores_abiertos'] }}</p>
                </div>
                <div class="rounded-lg bg-blue-50 p-1.5 text-blue-600">
                    <x-heroicon-o-pencil class="size-5" />
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400 group-hover:text-blue-600">Ver borradores →</p>
        </a>
        @endcan

        @can('albaranes.ver_todos')
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-slate-500">Este mes</p>
                    <p class="mt-1 text-3xl font-bold text-slate-700">{{ $kpis['albaranes_mes'] }}</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-1.5 text-slate-500">
                    <x-heroicon-o-calendar class="size-5" />
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400">Albaranes creados</p>
        </div>
        @endcan

        @can('clientes.ver')
        <a href="{{ route('clientes.index') }}"
           class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-primary-300 hover:shadow-md">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-slate-500">Clientes</p>
                    <p class="mt-1 text-3xl font-bold text-slate-700">{{ $kpis['clientes_activos'] }}</p>
                </div>
                <div class="rounded-lg bg-primary-50 p-1.5 text-primary-600">
                    <x-heroicon-o-building-office class="size-5" />
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400 group-hover:text-primary-600">Ver clientes →</p>
        </a>
        @endcan

        @can('usuarios.ver_todos')
        <a href="{{ route('usuarios.index') }}"
           class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-primary-300 hover:shadow-md">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-slate-500">Usuarios</p>
                    <p class="mt-1 text-3xl font-bold text-slate-700">{{ $kpis['usuarios_activos'] }}</p>
                </div>
                <div class="rounded-lg bg-primary-50 p-1.5 text-primary-600">
                    <x-heroicon-o-users class="size-5" />
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400 group-hover:text-primary-600">Ver usuarios →</p>
        </a>
        @endcan

    </div>

    {{-- Accesos rápidos --}}
    <div class="flex flex-wrap gap-2">
        @can('albaranes.crear_web')
        <a href="{{ route('albaranes.crear') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition-colors">
            <x-heroicon-o-plus class="size-4" />
            Nuevo albarán
        </a>
        @endcan

        @can('clientes.crear')
        <a href="{{ route('clientes.crear') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
            <x-heroicon-o-plus class="size-4" />
            Nuevo cliente
        </a>
        @endcan

        @can('proyectos.crear')
        <a href="{{ route('proyectos.crear') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-2 text-sm font-semibold text-white hover:bg-violet-700 transition-colors">
            <x-heroicon-o-plus class="size-4" />
            Nuevo proyecto
        </a>
        @endcan

        @can('usuarios.ver_todos')
        <a href="{{ route('usuarios.crear') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-700 transition-colors">
            <x-heroicon-o-plus class="size-4" />
            Nuevo usuario
        </a>
        @endcan
    </div>

    {{-- Tablas recientes --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Albaranes de este mes --}}
        @can('albaranes.ver_todos')
        <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3.5">
                <h3 class="text-sm font-semibold text-slate-800">
                    Albaranes de este mes
                    <span class="ml-1.5 text-xs font-normal text-slate-400">{{ now()->isoFormat('MMMM YYYY') }}</span>
                </h3>
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Nº</th>
                        <th class="px-4 py-2.5 text-left">Fecha</th>
                        <th class="px-4 py-2.5 text-left">Cliente</th>
                        <th class="px-4 py-2.5 text-left">Estado</th>
                        <th class="px-4 py-2.5 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ultimosAlbaranes as $albaran)
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer"
                            onclick="window.location='{{ route('albaranes.ver', $albaran) }}'">
                            <td class="px-4 py-2.5 font-mono text-xs font-medium text-primary-600">
                                {{ $albaran->numero }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-500 text-xs">
                                {{ $albaran->fecha?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 truncate max-w-[160px]">
                                {{ $albaran->cliente?->nombre ?? $albaran->cliente_texto ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5">
                                @php $estado = $albaran->estado; @endphp
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-amber-100 text-amber-700' => $estado === \App\Enums\EstadoAlbaran::PENDIENTE_FIRMA,
                                    'bg-green-100 text-green-700' => $estado === \App\Enums\EstadoAlbaran::FIRMADO,
                                    'bg-slate-100 text-slate-600' => $estado === \App\Enums\EstadoAlbaran::FACTURADO,
                                ])>
                                    {{ $estado->etiqueta() }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5" onclick="event.stopPropagation()">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('albaranes.ver', $albaran) }}"
                                       class="text-primary-500 hover:text-primary-700 transition-colors"
                                       title="Ver">
                                        <x-heroicon-o-eye class="size-4" />
                                    </a>
                                    <a href="{{ route('albaranes.editar', $albaran) }}"
                                       class="text-blue-500 hover:text-blue-700 transition-colors"
                                       title="Editar">
                                        <x-heroicon-o-pencil-square class="size-4" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-xs text-slate-400">
                                No hay albaranes este mes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($ultimosAlbaranes->isNotEmpty())
                <div class="border-t border-slate-100 px-5 py-3 text-right">
                    <a href="{{ route('albaranes.index') }}"
                       class="text-xs text-primary-600 hover:underline">Ver todos los albaranes →</a>
                </div>
            @endif
        </div>
        @endcan

        {{-- Borradores sin convertir --}}
        @can('borradores.ver_todos')
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3.5">
                <h3 class="text-sm font-semibold text-slate-800">Borradores pendientes</h3>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($ultimosBorradores as $borrador)
                    <li class="px-4 py-3 hover:bg-slate-50 transition-colors">
                        <a href="{{ route('borradores.ver', $borrador) }}" class="block">
                            <p class="font-mono text-xs font-semibold text-primary-600">
                                {{ $borrador->numero_borrador }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-600 truncate">
                                {{ $borrador->cliente?->nombre ?? $borrador->cliente_texto ?? '—' }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $borrador->fecha?->format('d/m/Y') }}
                            </p>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-xs text-slate-400">
                        No hay borradores pendientes.
                    </li>
                @endforelse
            </ul>
            @if ($ultimosBorradores->isNotEmpty())
                <div class="border-t border-slate-100 px-5 py-3 text-right">
                    <a href="{{ route('borradores.index') }}"
                       class="text-xs text-primary-600 hover:underline">Ver todos →</a>
                </div>
            @endif
        </div>
        @endcan

    </div>

</div>
