@props(['doc'])
{{--
    Resumen de Costes y Gastos en SOLO LECTURA (sin botones de edición).
    Reutilizable para Parte y Albarán: ambos comparten los mismos campos snapshot
    en sus líneas de personal y material. La edición vive en las pantallas Editar.
--}}
@php
    $fmtE = function ($v): string {
        return number_format((float) $v, 2, ',', '.');
    };
    $plusReten  = (bool) $doc->tiene_plus_retencion;
    $totalFact  = $doc->lineasPersonal->sum(fn ($l) => (float) $l->facturacion_snapshot + ($plusReten ? (float) $l->tarifa_plus_retencion_snapshot : 0));
    $totalCoste = $doc->lineasPersonal->sum(fn ($l) => (float) $l->coste_snapshot      + ($plusReten ? (float) $l->trabajador_tasa_plus_retencion_snapshot : 0));
    $totalMat      = \App\Support\Modulos::materialesAvanzado()
        ? $doc->lineasMaterial->sum(fn ($l) => (float) $l->cantidad * (float) $l->material_precio_venta_snapshot)
        : 0;
    $totalMatCoste = \App\Support\Modulos::materialesAvanzado()
        ? $doc->lineasMaterial->sum(fn ($l) => (float) $l->cantidad * (float) $l->material_precio_coste_snapshot)
        : 0;
    $granTotal  = $totalFact + $totalMat;
    $granCoste  = $totalCoste + $totalMatCoste;
@endphp

<div class="px-6 py-4">
    <div class="text-sm font-semibold text-slate-900">Costes y Gastos</div>
    <p class="mt-0.5 text-xs text-slate-400">Resumen financiero: facturación al cliente y coste de personal y materiales.</p>
</div>

{{-- Sub-sección: Personal --}}
<div class="border-t border-slate-200">
    <div class="bg-slate-50 px-6 py-2.5">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Personal</span>
    </div>
    @if ($doc->lineasPersonal->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <th class="px-6 py-2.5">Trabajador</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap">Horas / Extra</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Tarifa hora / extra (€/h) que se cobra al cliente">Tarifa/h · Extra/h</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Plus de retención cobrado al cliente">Plus ret.</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Total facturado al cliente para esta línea">Facturación</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Tasa hora / extra (€/h) que se paga al trabajador">Tasa/h · Extra/h</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Plus de retención pagado al trabajador">Plus ret.</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Total pagado al trabajador para esta línea">Coste</th>
                        <th class="px-3 py-2.5 text-right whitespace-nowrap">Margen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($doc->lineasPersonal as $linea)
                        @php
                            $fact   = (float) $linea->facturacion_snapshot + ($plusReten ? (float) $linea->tarifa_plus_retencion_snapshot : 0);
                            $gasto  = (float) $linea->coste_snapshot       + ($plusReten ? (float) $linea->trabajador_tasa_plus_retencion_snapshot : 0);
                            $margen = $fact - $gasto;
                        @endphp
                        <tr wire:key="costes-ro-{{ $linea->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-800 whitespace-nowrap">
                                {{ trim(($linea->trabajador_numero_empleado_snapshot ? $linea->trabajador_numero_empleado_snapshot.' · ' : '').trim(($linea->trabajador_apellidos_snapshot ?? '').' '.($linea->trabajador_nombre_snapshot ?? ''))) ?: '—' }}
                            </td>
                            <td class="px-3 py-3 text-right text-slate-600 tabular-nums whitespace-nowrap">
                                {{ $fmtE($linea->horas) }} / {{ $fmtE($linea->horas_extra) }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap">
                                <span class="text-emerald-700">{{ $fmtE($linea->tarifa_hora_snapshot) }}</span>
                                <span class="text-slate-400 mx-0.5">·</span>
                                <span class="text-emerald-600">{{ $fmtE($linea->tarifa_extra_snapshot) }}</span>
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap @if($plusReten) text-emerald-700 font-semibold @else text-slate-300 @endif">
                                {{ $plusReten ? $fmtE($linea->tarifa_plus_retencion_snapshot).' €' : '—' }}
                            </td>
                            <td class="px-3 py-3 text-right font-semibold tabular-nums text-emerald-700 whitespace-nowrap">
                                {{ $fmtE($fact) }} €
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap">
                                <span class="text-rose-700">{{ $fmtE($linea->trabajador_tasa_hora_snapshot) }}</span>
                                <span class="text-slate-400 mx-0.5">·</span>
                                <span class="text-rose-600">{{ $fmtE($linea->trabajador_tasa_extra_snapshot) }}</span>
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap @if($plusReten) text-rose-700 font-semibold @else text-slate-300 @endif">
                                {{ $plusReten ? $fmtE($linea->trabajador_tasa_plus_retencion_snapshot).' €' : '—' }}
                            </td>
                            <td class="px-3 py-3 text-right font-semibold tabular-nums text-rose-700 whitespace-nowrap">
                                {{ $fmtE($gasto) }} €
                            </td>
                            <td class="px-3 py-3 text-right font-semibold tabular-nums whitespace-nowrap @if($margen >= 0) text-slate-800 @else text-red-600 @endif">
                                {{ $fmtE($margen) }} €
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right uppercase tracking-wider text-slate-500">Totales personal</td>
                        <td class="px-3 py-3"></td>
                        <td class="px-3 py-3 text-right tabular-nums text-emerald-700 text-sm">{{ $fmtE($totalFact) }} €</td>
                        <td class="px-3 py-3"></td>
                        <td class="px-3 py-3"></td>
                        <td class="px-3 py-3 text-right tabular-nums text-rose-700 text-sm">{{ $fmtE($totalCoste) }} €</td>
                        <td class="px-3 py-3 text-right tabular-nums text-sm @if($totalFact - $totalCoste >= 0) text-slate-800 @else text-red-600 @endif">
                            {{ $fmtE($totalFact - $totalCoste) }} €
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="px-6 py-8 text-center text-sm text-slate-400">
            No hay trabajadores en este documento.
        </div>
    @endif
</div>

{{-- Sub-sección: Materiales --}}
@if(\App\Support\Modulos::materialesAvanzado())
<div class="border-t border-slate-200">
    <div class="bg-slate-50 px-6 py-2.5">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Materiales</span>
    </div>
    @if ($doc->lineasMaterial->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <th class="px-6 py-2.5">Material</th>
                        <th class="w-24 px-4 py-2.5 text-right">Cantidad</th>
                        <th class="w-16 px-4 py-2.5">Unidad</th>
                        <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap" title="Precio de venta al cliente">Venta/ud</th>
                        <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap">Total venta</th>
                        <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap" title="Precio de coste">Coste/ud</th>
                        <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap">Total coste</th>
                        <th class="w-24 px-4 py-2.5 text-right whitespace-nowrap">Margen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($doc->lineasMaterial as $linea)
                        @php
                            $totalVentaLinea = (float) $linea->cantidad * (float) $linea->material_precio_venta_snapshot;
                            $totalCosteLinea = (float) $linea->cantidad * (float) $linea->material_precio_coste_snapshot;
                            $margenLinea     = $totalVentaLinea - $totalCosteLinea;
                        @endphp
                        <tr wire:key="costes-mat-ro-{{ $linea->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $linea->material?->descripcion ?? $linea->material_descripcion_snapshot ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 tabular-nums">{{ number_format((float) $linea->cantidad, 2) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $linea->material?->unidad_medida ?? $linea->material_unidad_medida_snapshot ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-emerald-700 tabular-nums font-semibold">
                                {{ $linea->material_precio_venta_snapshot !== null ? $fmtE($linea->material_precio_venta_snapshot).' €' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-emerald-700 tabular-nums font-semibold">{{ $fmtE($totalVentaLinea) }} €</td>
                            <td class="px-4 py-3 text-right text-rose-700 tabular-nums font-semibold">
                                {{ $linea->material_precio_coste_snapshot !== null ? $fmtE($linea->material_precio_coste_snapshot).' €' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-rose-700 tabular-nums font-semibold">{{ $fmtE($totalCosteLinea) }} €</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold whitespace-nowrap @if($margenLinea >= 0) text-slate-800 @else text-red-600 @endif">
                                {{ $fmtE($margenLinea) }} €
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right uppercase tracking-wider text-slate-500">Totales materiales</td>
                        <td class="px-4 py-3 text-right tabular-nums text-emerald-700 text-sm">{{ $fmtE($totalMat) }} €</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right tabular-nums text-rose-700 text-sm">{{ $fmtE($totalMatCoste) }} €</td>
                        <td class="px-4 py-3 text-right tabular-nums text-sm @if($totalMat - $totalMatCoste >= 0) text-slate-800 @else text-red-600 @endif">
                            {{ $fmtE($totalMat - $totalMatCoste) }} €
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="px-6 py-8 text-center text-sm text-slate-400">
            No hay materiales en este documento.
        </div>
    @endif
</div>
@endif

{{-- Resumen global --}}
<div class="border-t-2 border-slate-300 bg-slate-100 px-6 py-4">
    <div class="flex justify-end gap-8">
        <div class="text-center">
            <div class="text-xs uppercase tracking-wider text-slate-500 mb-1">Facturación total</div>
            <div class="text-xl font-bold text-emerald-700 tabular-nums">{{ $fmtE($granTotal) }} €</div>
        </div>
        <div class="text-center">
            <div class="text-xs uppercase tracking-wider text-slate-500 mb-1">Coste total</div>
            <div class="text-xl font-bold text-rose-700 tabular-nums">{{ $fmtE($granCoste) }} €</div>
        </div>
        <div class="text-center">
            <div class="text-xs uppercase tracking-wider text-slate-500 mb-1">Margen</div>
            <div class="text-xl font-bold tabular-nums @if($granTotal - $granCoste >= 0) text-slate-800 @else text-red-600 @endif">
                {{ $fmtE($granTotal - $granCoste) }} €
            </div>
        </div>
    </div>
</div>
