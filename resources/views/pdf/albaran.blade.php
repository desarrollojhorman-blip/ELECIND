<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1e293b; }

    /* ── Cabecera ── */
    .header-table { width: 100%; border-collapse: collapse; padding-bottom: 10px; margin-bottom: 0; }
    .logo-cell { width: 60%; vertical-align: top; padding: 0 0 10px 0; }
    .logo-img { max-height: 72px; max-width: 220px; }
    .empresa-nombre { font-size: 16px; font-weight: 800; color: #1f2937; margin-bottom: 5px; }
    .empresa-datos { font-size: 10px; color: #475569; line-height: 1.6; }
    .empresa-datos .sub { font-weight: 600; }
    .num-cell { width: 40%; vertical-align: top; text-align: right; padding: 0 0 10px 0; }
    .num-table { border-collapse: collapse; border: 1px solid #e2e8f0; margin-left: auto; }
    .num-table td { padding: 4px 8px; font-size: 10px; border-top: 1px solid #f1f5f9; }
    .num-table .lbl { color: #64748b; white-space: nowrap; }
    .num-table .val { font-weight: 700; color: #1e293b; padding-left: 10px; font-size: 13px; }
    .num-table .val-sm { font-weight: 600; color: #1e293b; padding-left: 10px; }
    .num-table tr:first-child td { border-top: none; }

    /* ── Bloque cliente ── */
    .client-title { font-size: 9px; font-weight: 800; color: #111827; text-transform: uppercase; letter-spacing: .06em; margin: 12px 0 4px 2px; }
    .client-box { width: 100%; border: 1px solid #d1d5db; padding: 8px 12px; margin-bottom: 12px; }
    .client-box .row { font-size: 10px; color: #1f2937; line-height: 1.6; }
    .client-box .row.bold { font-weight: 700; }

    /* ── Tablas de líneas ── */
    .section-table { width: 100%; border-collapse: collapse; margin-bottom: 0; border: 1px solid #1f2937; }
    .section-table thead tr { background-color: #1f2937; }
    .section-table thead th { padding: 6px 10px; font-size: 10px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: .04em; text-align: left; border-right: 1px solid #475569; }
    .section-table thead th:last-child { border-right: none; }
    .section-table thead th.center { text-align: center; }
    .section-table tbody tr.odd  { background: #ffffff; }
    .section-table tbody tr.even { background: #f8fafc; }
    .section-table tbody td { padding: 7px 10px; font-size: 11px; color: #1e293b; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; vertical-align: middle; }
    .section-table tbody td:last-child { border-right: none; }
    .section-table tbody td.concepto { color: #475569; }
    .section-table tbody td.center { text-align: center; }
    .section-table tbody td.bold { font-weight: 600; }

    /* ── Observaciones ── */
    .obs { border-top: 1px solid #e2e8f0; padding: 8px 10px; font-size: 10px; color: #475569; }

    /* ── Firmas ── */
    .firmas-section { margin-top: 30px; }
    .firmas-table { width: 100%; border-collapse: collapse; }
    .firma-cell { width: 50%; vertical-align: top; padding: 0 8px 0 0; }
    .firma-cell:last-child { padding: 0 0 0 8px; }
    .firma-box { padding: 4px 0 0 0; }
    .firma-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
    .firma-img { max-width: 100%; max-height: 80px; display: block; margin: 4px auto; }
    .firma-fecha { font-size: 9px; color: #94a3b8; text-align: center; margin-top: 4px; }
    .firma-pendiente { font-size: 10px; color: #94a3b8; font-style: italic; text-align: center; padding: 12px 0; }

    /* ── Bloque de documento (sin marco) ── */
    .doc-wrap { }
    .doc-wrap + .doc-wrap { margin-top: 30px; }
</style>
</head>
<body>

@php
    $totalLineas = $albaran->lineasPersonal->count();
    $firmaTrabajador  = $albaran->firmas->where('tipo.value', 'trabajador')->first();
    $firmaResponsable = $albaran->firmas->where('tipo.value', 'responsable')->first();
@endphp

<div class="doc-wrap">

    {{-- ══ CABECERA ══ --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" class="logo-img" alt="{{ $empresa->nombre }}">
                @else
                    <div class="empresa-nombre">{{ $empresa->nombre }}</div>
                @endif
                <div class="empresa-datos">
                    @if ($empresa->razon_social)
                        <div class="sub">{{ $empresa->razon_social }}</div>
                    @endif
                    @if ($empresa->direccion)<div>{{ $empresa->direccion }}</div>@endif
                    @if ($empresa->codigo_postal || $empresa->poblacion)
                        <div>{{ trim($empresa->codigo_postal . ' ' . $empresa->poblacion) }}{{ $empresa->provincia ? ' (' . $empresa->provincia . ')' : '' }}</div>
                    @endif
                    @if ($empresa->telefono || $empresa->movil)
                        <div>
                            @if ($empresa->telefono)Tlf. {{ $empresa->telefono }}@endif
                            @if ($empresa->telefono && $empresa->movil) &nbsp;·&nbsp; @endif
                            @if ($empresa->movil)Móvil {{ $empresa->movil }}@endif
                        </div>
                    @endif
                    @if ($empresa->web || $empresa->email_contacto)
                        <div>
                            @if ($empresa->web){{ $empresa->web }}@endif
                            @if ($empresa->web && $empresa->email_contacto) &nbsp;·&nbsp; @endif
                            @if ($empresa->email_contacto){{ $empresa->email_contacto }}@endif
                        </div>
                    @endif
                </div>
            </td>
            <td class="num-cell">
                <table class="num-table">
                    <tr>
                        <td class="lbl">Nº Albarán</td>
                        <td class="val">{{ $albaran->numero }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Fecha</td>
                        <td class="val-sm">{{ $albaran->fecha->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Tipo jornada</td>
                        <td class="val-sm">{{ $albaran->tipo_hora->etiqueta() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ BLOQUE CLIENTE (título fuera + rectángulo) ══ --}}
    @php
        $cli = $albaran->cliente;
        $cliPoblacion = $cli
            ? trim(($cli->codigo_postal ? $cli->codigo_postal.' ' : '').($cli->poblacion ?? '')).
              ($cli->provincia ? ' ('.$cli->provincia.')' : '')
            : '';
    @endphp
    <div class="client-title">Datos del cliente</div>
    <div class="client-box">
        <div class="row bold">{{ $cli?->nombre ?? '—' }}</div>
        @if ($cli?->direccion)
            <div class="row">{{ $cli->direccion }}</div>
        @endif
        @if ($cliPoblacion !== '')
            <div class="row">{{ $cliPoblacion }}</div>
        @endif
    </div>

    {{-- ══ TRABAJADORES ══ --}}
    @if ($albaran->lineasPersonal->isNotEmpty())
        <table class="section-table">
            <thead>
                <tr>
                    <th>Trabajo realizado</th>
                    <th>Nombre del trabajador</th>
                    <th class="center">Horas normales</th>
                    <th class="center">Horas extras</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($albaran->lineasPersonal as $i => $linea)
                    <tr class="{{ $i % 2 === 0 ? 'odd' : 'even' }}">
                        @if ($i === 0)
                            <td rowspan="{{ $totalLineas }}" class="concepto" style="vertical-align: middle;">
                                {{ $albaran->concepto?->nombre ?? '—' }}
                            </td>
                        @endif
                        <td>{{ trim(($linea->trabajador->nombre ?? '') . ' ' . ($linea->trabajador->apellidos ?? '')) ?: '—' }}</td>
                        <td class="center bold">{{ number_format((float) $linea->horas, 2) }}</td>
                        <td class="center">{{ number_format((float) $linea->horas_extra, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══ OBSERVACIONES (dentro del bloque principal) ══ --}}
    @if ($albaran->observaciones && (!$conMateriales || $albaran->lineasMaterial->isEmpty()))
        <div class="obs"><strong>Observaciones:</strong> {{ $albaran->observaciones }}</div>
    @endif

</div>{{-- /doc-wrap principal --}}

{{-- ══ MATERIALES (bloque separado) ══ --}}
@if ($conMateriales && $albaran->lineasMaterial->isNotEmpty())
    <div class="doc-wrap" style="margin-top: 30px;">
        <table class="section-table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="center" style="width: 25%;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($albaran->lineasMaterial as $i => $linea)
                    <tr class="{{ $i % 2 === 0 ? 'odd' : 'even' }}">
                        <td>{{ $linea->material->descripcion ?? '—' }}</td>
                        <td class="center">{{ number_format((float) $linea->cantidad, 2) }} {{ $linea->material->unidad_medida ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($albaran->observaciones)
            <div class="obs"><strong>Observaciones:</strong> {{ $albaran->observaciones }}</div>
        @endif
    </div>
@endif

{{-- ══ FIRMAS ══ --}}
<div class="firmas-section">
    <table class="firmas-table">
        <tr>
            <td class="firma-cell">
                <div class="firma-box">
                    <div class="firma-label">Firma del empleado</div>
                    @if ($firmaTrabajador)
                        @php $firmaPath = \Illuminate\Support\Facades\Storage::disk('public')->path($firmaTrabajador->firma_path); @endphp
                        @if (file_exists($firmaPath))
                            <img src="{{ $firmaPath }}" class="firma-img" alt="Firma empleado">
                        @endif
                        <div class="firma-fecha">Firmado el {{ $firmaTrabajador->firmado_at->format('d/m/Y H:i') }}</div>
                    @else
                        <div class="firma-pendiente">Pendiente de firma</div>
                    @endif
                </div>
            </td>
            <td class="firma-cell" style="padding: 0 0 0 8px;">
                <div class="firma-box">
                    <div class="firma-label">Firma del responsable</div>
                    @if ($firmaResponsable)
                        @php $firmaPath = \Illuminate\Support\Facades\Storage::disk('public')->path($firmaResponsable->firma_path); @endphp
                        @if (file_exists($firmaPath))
                            <img src="{{ $firmaPath }}" class="firma-img" alt="Firma responsable">
                        @endif
                        <div class="firma-fecha">Firmado el {{ $firmaResponsable->firmado_at->format('d/m/Y H:i') }}</div>
                    @else
                        <div class="firma-pendiente">Pendiente de firma</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
