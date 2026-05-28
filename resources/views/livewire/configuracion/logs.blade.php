<div>
    <x-ui.page-header title="Logs de auditoría" subtitle="Registro de acciones realizadas en el sistema. Solo lectura." />

    {{-- ── Filtros ──────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">

        <div wire:key="filtros-logs-{{ $filtrosVersion }}">
            {{-- Fila 1: buscar · usuario · entidad · evento --}}
            <div class="grid grid-cols-4 gap-3">
                <x-ui.field label="Buscar">
                    <x-ui.input wire:model.live.debounce.400ms="busqueda" placeholder="Descripción…" />
                </x-ui.field>

                <x-ui.field label="Usuario">
                    <x-ui.searchable-select
                        wire-model="filtroUsuario"
                        :value="$filtroUsuario"
                        :options="$this->usuarios->map(fn($u) => ['value' => $u->id, 'label' => trim($u->apellidos.' '.$u->nombre)])"
                        placeholder="Todos"
                    />
                </x-ui.field>

                <x-ui.field label="Entidad">
                    <x-ui.select wire:model.live="filtroModelo">
                        <option value="">Todas</option>
                        @foreach ($modelos as $class => $label)
                            <option value="{{ $class }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Evento">
                    <x-ui.select wire:model.live="filtroEvento">
                        <option value="">Todos</option>
                        @foreach ($eventos as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            {{-- Fila 2: fechas --}}
            <div class="mt-3 grid grid-cols-4 gap-3">
                <x-ui.field label="Desde">
                    <x-ui.date-input wireModel="fechaDesde" :value="$fechaDesde" :live="true" placeholder="dd/mm/aaaa" />
                </x-ui.field>

                <x-ui.field label="Hasta">
                    <x-ui.date-input wireModel="fechaHasta" :value="$fechaHasta" :live="true" placeholder="dd/mm/aaaa" />
                </x-ui.field>
            </div>
        </div>

        @if ($busqueda || $filtroUsuario || $filtroModelo || $filtroEvento || $fechaDesde || $fechaHasta)
            <div class="mt-3 flex justify-end">
                <button wire:click="limpiarFiltros"
                        class="text-xs text-primary-600 underline hover:text-primary-800">
                    Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- ── Tabla ─────────────────────────────────────────────────── --}}
    @php $logs = $this->logs; @endphp

    @if ($logs->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm">
            No hay registros para los filtros seleccionados.
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="bg-slate-800 text-left text-xs font-bold uppercase tracking-wider text-white">
                        <th class="px-4 py-3 whitespace-nowrap">Fecha / Hora</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">IP</th>
                        <th class="px-4 py-3">Evento</th>
                        <th class="px-4 py-3">Entidad</th>
                        <th class="px-4 py-3">Descripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        @php
                            $cambios    = $this->cambios($log);
                            $etiqueta   = $this->etiquetaEvento($log);
                            $claseEvento = $this->claseEvento($log);
                            $modelo     = $this->etiquetaModelo($log->subject_type);
                            $ip         = $this->ip($log);
                            $navegador  = $this->navegador($log);
                        @endphp
                        <tr class="hover:bg-slate-50">

                            {{-- Fecha --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="block font-mono text-xs text-slate-800">
                                    {{ $log->created_at->format('d/m/Y') }}
                                </span>
                                <span class="block font-mono text-xs text-slate-400">
                                    {{ $log->created_at->format('H:i:s') }}
                                </span>
                            </td>

                            {{-- Usuario --}}
                            <td class="px-4 py-3">
                                @if ($log->causer)
                                    <span class="font-medium text-slate-800">
                                        {{ trim($log->causer->apellidos . ' ' . $log->causer->nombre) }}
                                    </span>
                                    <span class="block text-xs text-slate-400">{{ $log->causer->username }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- IP / Navegador --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($ip)
                                    <span class="block font-mono text-xs text-slate-700">{{ $ip }}</span>
                                @else
                                    <span class="block text-xs text-slate-400">—</span>
                                @endif
                                @if ($navegador)
                                    <span class="block text-xs text-slate-400">{{ $navegador }}</span>
                                @endif
                            </td>

                            {{-- Evento --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $claseEvento }}">
                                    {{ $etiqueta }}
                                </span>
                            </td>

                            {{-- Entidad --}}
                            <td class="px-4 py-3">
                                @if ($log->subject_type)
                                    <span class="inline-block rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">
                                        {{ $modelo }}
                                    </span>
                                    @if ($log->subject_id)
                                        <span class="ml-1 font-mono text-xs text-slate-400">#{{ $log->subject_id }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Descripción / Cambios --}}
                            <td class="px-4 py-3">
                                <p class="text-slate-700">{{ $log->description }}</p>

                                @if (! empty($cambios))
                                    <div class="mt-1.5 space-y-0.5">
                                        @foreach ($cambios as $cambio)
                                            <div class="flex flex-wrap items-baseline gap-x-1 text-xs">
                                                <span class="font-mono text-slate-500">{{ $cambio['campo'] }}:</span>
                                                <span class="text-red-600 line-through">{{ $cambio['anterior'] }}</span>
                                                <span class="text-slate-400">→</span>
                                                <span class="text-green-700">{{ $cambio['nuevo'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif

    {{-- Loading --}}
    <div wire:loading.flex class="fixed inset-0 z-50 items-center justify-center bg-white/60 backdrop-blur-sm">
        <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 shadow-lg">
            <svg class="size-5 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-slate-600">Cargando…</span>
        </div>
    </div>
</div>
