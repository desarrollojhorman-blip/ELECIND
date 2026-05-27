<div>
    {{-- ── Rango de fechas (sticky) ───────────────────────────────── --}}
    <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-4 py-2.5">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Desde</label>
                <x-ui.date-input wireModel="fechaDesde" :live="true" placeholder="dd/mm/aaaa" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Hasta</label>
                <x-ui.date-input wireModel="fechaHasta" :live="true" placeholder="dd/mm/aaaa" />
            </div>
        </div>
    </div>

    <div class="px-4 py-3">

        {{-- ── Total hero ──────────────────────────────────────────── --}}
        <div class="mb-3 rounded-xl bg-slate-800 px-4 py-4 text-center text-white">
            <div class="text-xs font-semibold uppercase tracking-widest text-slate-400">Total este mes</div>
            <div class="mt-1 text-4xl font-black tabular-nums">
                {{ number_format($totales['total'], 2) }}h
            </div>
        </div>

        {{-- ── Desglose por tipo ───────────────────────────────────── --}}
        <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
            {{-- Fila 1: horas normales --}}
            <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                @foreach ([
                    ['dot' => 'bg-slate-400',  'label' => 'Laboral',     'key' => 'laboral'],
                    ['dot' => 'bg-blue-400',   'label' => 'Lab. Noche',  'key' => 'laboral_noche'],
                ] as $item)
                    <div class="px-3 py-3 text-center">
                        <div class="mb-1 flex items-center justify-center gap-1">
                            <span class="inline-block size-2 rounded-full {{ $item['dot'] }}"></span>
                            <span class="text-xs text-slate-500">{{ $item['label'] }}</span>
                        </div>
                        <div @class([
                            'text-xl font-bold tabular-nums',
                            'text-slate-800' => $totales[$item['key']] > 0,
                            'text-slate-300' => $totales[$item['key']] === 0.0,
                        ])>{{ number_format($totales[$item['key']], 2) }}h</div>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                @foreach ([
                    ['dot' => 'bg-red-400',    'label' => 'Festivo',     'key' => 'festivo'],
                    ['dot' => 'bg-violet-400', 'label' => 'Fest. Noche', 'key' => 'festivo_noche'],
                ] as $item)
                    <div class="px-3 py-3 text-center">
                        <div class="mb-1 flex items-center justify-center gap-1">
                            <span class="inline-block size-2 rounded-full {{ $item['dot'] }}"></span>
                            <span class="text-xs text-slate-500">{{ $item['label'] }}</span>
                        </div>
                        <div @class([
                            'text-xl font-bold tabular-nums',
                            'text-slate-800' => $totales[$item['key']] > 0,
                            'text-slate-300' => $totales[$item['key']] === 0.0,
                        ])>{{ number_format($totales[$item['key']], 2) }}h</div>
                    </div>
                @endforeach
            </div>

            {{-- Fila extras --}}
            <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100 bg-amber-50/60">
                @foreach ([
                    ['label' => '+ Lab. Extra',     'key' => 'laboral_extra'],
                    ['label' => '+ Lab. Noche Ext.','key' => 'laboral_noche_extra'],
                ] as $item)
                    <div class="px-3 py-2.5 text-center">
                        <div class="text-xs text-amber-600">{{ $item['label'] }}</div>
                        <div @class([
                            'text-lg font-bold tabular-nums',
                            'text-amber-700' => $totales[$item['key']] > 0,
                            'text-amber-200' => $totales[$item['key']] === 0.0,
                        ])>{{ number_format($totales[$item['key']], 2) }}h</div>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 divide-x divide-slate-100 bg-amber-50/60">
                @foreach ([
                    ['label' => '+ Fest. Extra',      'key' => 'festivo_extra'],
                    ['label' => '+ Fest. Noche Ext.', 'key' => 'festivo_noche_extra'],
                ] as $item)
                    <div class="px-3 py-2.5 text-center">
                        <div class="text-xs text-amber-600">{{ $item['label'] }}</div>
                        <div @class([
                            'text-lg font-bold tabular-nums',
                            'text-amber-700' => $totales[$item['key']] > 0,
                            'text-amber-200' => $totales[$item['key']] === 0.0,
                        ])>{{ number_format($totales[$item['key']], 2) }}h</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Lista de días ───────────────────────────────────────── --}}
        @if ($lineas->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <x-heroicon-o-clock class="mb-2 size-10 text-slate-200" />
                <p class="text-sm text-slate-400">Sin registros este mes</p>
            </div>
        @else
            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Días trabajados ({{ $lineas->count() }})
            </div>
            <div class="space-y-2">
                @foreach ($lineas as $linea)
                    @php
                        $fecha       = \Illuminate\Support\Carbon::parse($linea->fecha);
                        $esFinSemana = $fecha->isWeekend();
                        $esFestivo   = str_starts_with($linea->tipo_hora, 'festivo');
                        $tieneExtra  = (float) $linea->horas_extra > 0;

                        $borderColor = match($linea->tipo_hora) {
                            'laboral_noche' => 'border-l-blue-400',
                            'festivo'       => 'border-l-red-400',
                            'festivo_noche' => 'border-l-violet-400',
                            default         => 'border-l-slate-300',
                        };

                        $badgeBg = match($linea->tipo_hora) {
                            'laboral'       => 'bg-slate-100 text-slate-700',
                            'laboral_noche' => 'bg-blue-100 text-blue-800',
                            'festivo'       => 'bg-red-100 text-red-800',
                            'festivo_noche' => 'bg-violet-100 text-violet-800',
                            default         => 'bg-slate-100 text-slate-700',
                        };

                        $etiqueta = match($linea->tipo_hora) {
                            'laboral'       => 'Laboral',
                            'laboral_noche' => 'Noche',
                            'festivo'       => 'Festivo',
                            'festivo_noche' => 'F.Noche',
                            default         => $linea->tipo_hora,
                        };
                    @endphp

                    <a href="{{ route('mobile.albaranes.ver', $linea->albaran_id) }}"
                       wire:navigate
                       class="flex items-start gap-3 rounded-xl border border-slate-200 border-l-4 {{ $borderColor }} bg-white p-3 shadow-sm active:scale-[0.99] active:transition-transform">

                        {{-- Fecha --}}
                        <div class="w-10 shrink-0 text-center">
                            <span @class([
                                'block text-2xl font-black leading-none tabular-nums',
                                'text-red-500'   => $esFinSemana || $esFestivo,
                                'text-slate-800' => ! $esFinSemana && ! $esFestivo,
                            ])>{{ $fecha->format('d') }}</span>
                            <span class="block text-xs text-slate-400">{{ $diasSemana[$fecha->dayOfWeek] }}</span>
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            @if ($linea->cliente_nombre)
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $linea->cliente_nombre }}</p>
                            @endif
                            @if ($linea->proyecto_nombre)
                                <p class="truncate text-xs text-slate-500">{{ $linea->proyecto_nombre }}</p>
                            @endif
                            @if ($linea->concepto_nombre)
                                <p class="mt-0.5 truncate text-xs text-slate-400">{{ $linea->concepto_nombre }}</p>
                            @endif
                        </div>

                        {{-- Horas --}}
                        <div class="shrink-0 text-right">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeBg }}">
                                {{ $etiqueta }}
                            </span>
                            <div class="mt-1 text-sm font-bold tabular-nums text-slate-800">
                                {{ number_format((float) $linea->horas, 2) }}h
                            </div>
                            @if ($tieneExtra)
                                <div class="text-xs font-semibold text-amber-600">
                                    +{{ number_format((float) $linea->horas_extra, 2) }}h
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Loading --}}
    <div wire:loading.flex class="fixed inset-0 z-50 items-center justify-center bg-white/70">
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
            <svg class="size-4 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-slate-600">Cargando…</span>
        </div>
    </div>
</div>
