<div>
    <x-ui.page-header title="Control de Horas" subtitle="Detalle diario de horas trabajadas por empleado." />

    {{-- ── Filtros ──────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Trabajador</label>
                <select wire:model.live="filtroTrabajador"
                        class="w-full rounded-md border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    @foreach ($this->trabajadoresDisponibles as $trab)
                        <option value="{{ $trab->id }}">{{ trim($trab->apellidos . ' ' . $trab->nombre) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Cliente</label>
                <select wire:model.live="filtroCliente"
                        class="w-full rounded-md border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    @foreach ($this->clientesDisponibles as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Proyecto</label>
                <select wire:model.live="filtroProyecto"
                        class="w-full rounded-md border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    @foreach ($this->proyectosDisponibles as $proyecto)
                        <option value="{{ $proyecto->id }}">
                            @if ($proyecto->codigo)[{{ $proyecto->codigo }}] @endif{{ $proyecto->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Fecha inicio</label>
                <x-ui.date-input wireModel="fechaDesde" :live="true" placeholder="dd/mm/aaaa" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Fecha fin</label>
                <x-ui.date-input wireModel="fechaHasta" :live="true" placeholder="dd/mm/aaaa" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Estado albarán</label>
                <select wire:model.live="filtroEstado"
                        class="w-full rounded-md border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->value }}">{{ $estado->etiqueta() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Leyenda + acciones ───────────────────────────────────── --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-3.5 w-5 rounded border border-slate-300 bg-white"></span>
                Laboral
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-3.5 w-5 rounded border border-blue-300 bg-blue-100"></span>
                Lab. Noche
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-3.5 w-5 rounded border border-red-300 bg-red-100"></span>
                Festivo
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-3.5 w-5 rounded border border-violet-300 bg-violet-100"></span>
                Fest. Noche
            </span>
        </div>
        <x-ui.button variant="outline" icon="heroicon-o-arrow-down-tray" disabled>
            Exportar Excel
            <x-ui.badge tone="neutral" class="ml-1">Pronto</x-ui.badge>
        </x-ui.button>
    </div>

    {{-- ── Tabla ────────────────────────────────────────────────── --}}
    @php $lineas = $this->lineasDiarias; @endphp

    @if ($lineas->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm">
            No hay registros para el período y filtros seleccionados.
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="bg-slate-800 text-left text-xs font-bold uppercase tracking-wider text-white">
                        <th class="px-4 py-3">Día</th>
                        @if (! $filtroTrabajador)
                            <th class="px-4 py-3">Trabajador</th>
                        @endif
                        <th class="px-4 py-3">Nº Albarán</th>
                        <th class="px-4 py-3">Cliente / Proyecto</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Tipo jornada</th>
                        <th class="px-4 py-3 text-right">Horas</th>
                        <th class="px-4 py-3 text-right">H. Extra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($lineas as $linea)
                        @php
                            $fecha       = \Illuminate\Support\Carbon::parse($linea->fecha);
                            $esFinSemana = $fecha->isWeekend();
                            $esFestivo   = str_starts_with($linea->tipo_hora, 'festivo');
                            $tieneExtra  = (float) $linea->horas_extra > 0;

                            $rowBg = match($linea->tipo_hora) {
                                'laboral_noche' => 'bg-blue-50',
                                'festivo'       => 'bg-red-50',
                                'festivo_noche' => 'bg-violet-50',
                                default         => '',
                            };

                            $badgeColor = match($linea->tipo_hora) {
                                'laboral'       => 'border border-slate-200 bg-slate-100 text-slate-700',
                                'laboral_noche' => 'border border-blue-200 bg-blue-100 text-blue-800',
                                'festivo'       => 'border border-red-200 bg-red-100 text-red-800',
                                'festivo_noche' => 'border border-violet-200 bg-violet-100 text-violet-800',
                                default         => 'border border-slate-200 bg-slate-100 text-slate-700',
                            };

                            $etiquetaHora = match($linea->tipo_hora) {
                                'laboral'       => 'Laboral',
                                'laboral_noche' => 'Lab. Noche',
                                'festivo'       => 'Festivo',
                                'festivo_noche' => 'Fest. Noche',
                                default         => $linea->tipo_hora,
                            };
                        @endphp
                        <tr class="{{ $rowBg }} transition-colors hover:brightness-95">

                            {{-- Día --}}
                            <td class="px-4 py-3">
                                <span @class([
                                    'block text-lg font-bold leading-none tabular-nums',
                                    'text-red-600'   => $esFinSemana || $esFestivo,
                                    'text-slate-800' => ! $esFinSemana && ! $esFestivo,
                                ])>{{ $fecha->format('d') }}</span>
                                <span class="block text-xs text-slate-500">{{ $diasSemana[$fecha->dayOfWeek] }}</span>
                                <span class="block text-xs text-slate-400">{{ $fecha->format('m/Y') }}</span>
                            </td>

                            {{-- Trabajador (solo si no hay filtro) --}}
                            @if (! $filtroTrabajador)
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ trim($linea->trabajador_apellidos . ' ' . $linea->trabajador_nombre) ?: '—' }}
                                </td>
                            @endif

                            {{-- Nº Albarán --}}
                            <td class="px-4 py-3">
                                <a href="{{ route('albaranes.ver', $linea->albaran_id) }}"
                                   wire:navigate
                                   class="font-mono text-xs font-medium text-primary-600 hover:underline">
                                    {{ $linea->albaran_numero }}
                                </a>
                            </td>

                            {{-- Cliente / Proyecto --}}
                            <td class="px-4 py-3">
                                @if ($linea->cliente_nombre)
                                    <div class="font-medium text-slate-800">{{ $linea->cliente_nombre }}</div>
                                @endif
                                @if ($linea->proyecto_nombre)
                                    <div class="text-xs text-slate-500">
                                        @if ($linea->proyecto_codigo)
                                            <span class="font-mono">{{ $linea->proyecto_codigo }}</span> ·
                                        @endif
                                        {{ $linea->proyecto_nombre }}
                                    </div>
                                @endif
                            </td>

                            {{-- Concepto --}}
                            <td class="px-4 py-3 text-slate-700">
                                {{ $linea->concepto_nombre ?: '—' }}
                            </td>

                            {{-- Tipo jornada --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeColor }}">
                                    {{ $etiquetaHora }}
                                </span>
                            </td>

                            {{-- Horas --}}
                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-slate-800">
                                {{ number_format((float) $linea->horas, 2) }}h
                            </td>

                            {{-- H. Extra --}}
                            <td class="px-4 py-3 text-right tabular-nums">
                                @if ($tieneExtra)
                                    <span class="font-semibold text-amber-700">{{ number_format((float) $linea->horas_extra, 2) }}h</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Barra de totales --}}
        <div class="mt-4 overflow-hidden rounded-lg shadow-sm">
            {{-- Total general --}}
            <div class="bg-slate-800 px-6 py-3 text-center text-white">
                <div class="text-xs font-semibold uppercase tracking-widest text-slate-300">Total Horas</div>
                <div class="mt-0.5 text-3xl font-black tabular-nums">{{ number_format($totales['total'], 2) }}h</div>
            </div>

            {{-- Fila 1: horas normales --}}
            <div class="grid grid-cols-4 divide-x divide-slate-200 border-b border-slate-200 bg-white">
                @foreach ([
                    ['color' => 'bg-slate-300',  'label' => 'Laboral',      'key' => 'laboral'],
                    ['color' => 'bg-blue-400',   'label' => 'Lab. Noche',   'key' => 'laboral_noche'],
                    ['color' => 'bg-red-400',    'label' => 'Festivo',      'key' => 'festivo'],
                    ['color' => 'bg-violet-400', 'label' => 'Fest. Noche',  'key' => 'festivo_noche'],
                ] as $item)
                    <div class="px-3 py-2.5 text-center">
                        <div class="mb-1 inline-block h-1 w-5 rounded-full {{ $item['color'] }}"></div>
                        <div class="text-xs text-slate-500">{{ $item['label'] }}</div>
                        <div class="text-base font-bold tabular-nums text-slate-800">{{ number_format($totales[$item['key']], 2) }}h</div>
                    </div>
                @endforeach
            </div>

            {{-- Fila 2: horas extra por tipo --}}
            <div class="grid grid-cols-4 divide-x divide-slate-200 bg-amber-50">
                @foreach ([
                    ['label' => '+ Lab. Extra',      'key' => 'laboral_extra'],
                    ['label' => '+ Lab. Noche Extra', 'key' => 'laboral_noche_extra'],
                    ['label' => '+ Fest. Extra',      'key' => 'festivo_extra'],
                    ['label' => '+ Fest. Noche Extra','key' => 'festivo_noche_extra'],
                ] as $item)
                    <div class="px-3 py-2.5 text-center">
                        <div class="mb-1 inline-block h-1 w-5 rounded-full bg-amber-400"></div>
                        <div class="text-xs text-amber-700">{{ $item['label'] }}</div>
                        <div class="text-base font-bold tabular-nums text-amber-700">{{ number_format($totales[$item['key']], 2) }}h</div>
                    </div>
                @endforeach
            </div>
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
