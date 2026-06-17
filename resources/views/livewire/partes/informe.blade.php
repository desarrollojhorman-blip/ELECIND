<div>
    <x-ui.page-header
        title="Informe de horas"
        subtitle="Cuánto se paga a cada trabajador y cuánto se cobra a cada cliente en un rango de fechas. Cálculo provisional (solo horas, sin pluses)."
    />

    <x-ui.flash />

    @php
        // Formato € con coma decimal, sin ceros sobrantes innecesarios.
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.');
        $hrs = function ($v) {
            $v = (float) $v;
            return rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
        };
        $pct = fn ($v) => $v === null ? '—' : number_format($v * 100, 1, ',', '.').'%';

        $etiquetas = ['trabajador' => 'Trabajador', 'cliente' => 'Cliente', 'proyecto' => 'Proyecto'];
    @endphp

    {{-- ── Filtros: rango + perspectiva + desglose ──────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Rango de fechas --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Rango de fechas</label>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="date" wire:model.live="fechaDesde"
                           class="rounded-md border-slate-300 py-1.5 text-sm focus:border-primary-500 focus:ring-primary-500" />
                    <span class="text-slate-400">→</span>
                    <input type="date" wire:model.live="fechaHasta"
                           class="rounded-md border-slate-300 py-1.5 text-sm focus:border-primary-500 focus:ring-primary-500" />
                    <button type="button" wire:click="rangoMesActual"
                            class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50">
                        Mes actual
                    </button>
                    <button type="button" wire:click="rangoAnioActual"
                            class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50">
                        Año actual
                    </button>
                </div>
            </div>

            {{-- Ver por (fila principal) --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Ver por</label>
                <div class="inline-flex rounded-md border border-slate-300 p-0.5">
                    @foreach ($etiquetas as $valor => $texto)
                        <button type="button" wire:click="setAgrupacion('{{ $valor }}')"
                                @class([
                                    'rounded px-3 py-1.5 text-sm font-medium transition-colors',
                                    'bg-primary-600 text-white' => $agrupacion === $valor,
                                    'text-slate-600 hover:bg-slate-50' => $agrupacion !== $valor,
                                ])>
                            {{ $texto }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Desglosar por (sub-filas) --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Desglosar por</label>
                <div class="inline-flex flex-wrap rounded-md border border-slate-300 p-0.5">
                    <button type="button" wire:click="setDesglose('')"
                            @class([
                                'rounded px-3 py-1.5 text-sm font-medium transition-colors',
                                'bg-slate-700 text-white' => $desglose === '',
                                'text-slate-600 hover:bg-slate-50' => $desglose !== '',
                            ])>
                        Sin desglose
                    </button>
                    @foreach ($etiquetas as $valor => $texto)
                        @if ($valor !== $agrupacion)
                            <button type="button" wire:click="setDesglose('{{ $valor }}')"
                                    @class([
                                        'rounded px-3 py-1.5 text-sm font-medium transition-colors',
                                        'bg-primary-600 text-white' => $desglose === $valor,
                                        'text-slate-600 hover:bg-slate-50' => $desglose !== $valor,
                                    ])>
                                {{ $texto }}
                            </button>
                        @endif
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-slate-400">
                    @if ($tieneDesglose)
                        Pulsa una fila para ver qué aporta cada {{ strtolower($etiquetas[$desglose]) }} dentro de cada {{ strtolower($etiquetas[$agrupacion]) }}.
                    @else
                        Activa un desglose para abrir cada fila.
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-3 flex justify-end">
            <button type="button" wire:click="exportar"
                    class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">
                <x-heroicon-o-arrow-down-tray class="size-4" />
                Exportar Excel
            </button>
        </div>
    </div>

    {{-- ── Tabla ────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-primary-700 text-xs font-semibold uppercase tracking-wide text-table-header-text">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            {{ $etiquetas[$agrupacion] }}@if ($tieneDesglose) <span class="font-normal normal-case opacity-80">› {{ $etiquetas[$desglose] }}</span>@endif
                        </th>
                        <th class="px-4 py-3 text-right">Horas</th>
                        <th class="px-4 py-3 text-right">A pagar €</th>
                        <th class="px-4 py-3 text-right">A cobrar €</th>
                        <th class="px-4 py-3 text-right">Margen €</th>
                        <th class="px-4 py-3 text-right">€/h cobro</th>
                        <th class="px-4 py-3 text-right">€/h coste</th>
                        <th class="px-4 py-3 text-right">% Margen</th>
                    </tr>
                </thead>

                @forelse ($filas as $f)
                    <tbody class="border-b border-slate-100" @if ($tieneDesglose) x-data="{ open: false }" @endif>
                        {{-- Fila principal --}}
                        <tr @class(['transition-colors hover:bg-slate-50', 'cursor-pointer' => $tieneDesglose])
                            @if ($tieneDesglose) @click="open = ! open" @endif>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <span class="inline-flex items-center gap-1.5">
                                    @if ($tieneDesglose && $f['hijos']->isNotEmpty())
                                        <x-heroicon-m-chevron-right class="size-4 shrink-0 text-slate-400 transition-transform"
                                            x-bind:class="open && 'rotate-90'" />
                                    @else
                                        <span class="inline-block size-4 shrink-0"></span>
                                    @endif
                                    {{ $f['etiqueta'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700">{{ $hrs($f['horas']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium text-emerald-700">{{ $eur($f['coste']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium text-blue-700">{{ $eur($f['facturacion']) }}</td>
                            <td @class(['px-4 py-3 text-right tabular-nums', 'text-rose-600' => $f['margen'] < 0, 'text-slate-700' => $f['margen'] >= 0])>{{ $eur($f['margen']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ $eur($f['precio_hora']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ $eur($f['coste_hora']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ $pct($f['pct_margen']) }}</td>
                        </tr>

                        {{-- Sub-filas (desglose) --}}
                        @if ($tieneDesglose)
                            @foreach ($f['hijos'] as $h)
                                <tr x-show="open" x-cloak class="bg-slate-50/60">
                                    <td class="py-2 pl-12 pr-4 text-slate-600">↳ {{ $h['etiqueta'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums text-slate-600">{{ $hrs($h['horas']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums text-emerald-600">{{ $eur($h['coste']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums text-blue-600">{{ $eur($h['facturacion']) }}</td>
                                    <td @class(['px-4 py-2 text-right tabular-nums', 'text-rose-600' => $h['margen'] < 0, 'text-slate-600' => $h['margen'] >= 0])>{{ $eur($h['margen']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums text-slate-400">{{ $eur($h['precio_hora']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums text-slate-400">{{ $eur($h['coste_hora']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums text-slate-400">{{ $pct($h['pct_margen']) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-slate-500">
                                No hay horas imputadas en el rango seleccionado.
                            </td>
                        </tr>
                    </tbody>
                @endforelse

                @if ($filas->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50 font-semibold">
                            <td class="px-4 py-3 text-slate-900">TOTAL</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-900">{{ $hrs($total['horas']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-emerald-700">{{ $eur($total['coste']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-blue-700">{{ $eur($total['facturacion']) }}</td>
                            <td @class(['px-4 py-3 text-right tabular-nums', 'text-rose-600' => $total['margen'] < 0, 'text-slate-900' => $total['margen'] >= 0])>{{ $eur($total['margen']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ $eur($total['precio_hora']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ $eur($total['coste_hora']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ $pct($total['pct_margen']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
