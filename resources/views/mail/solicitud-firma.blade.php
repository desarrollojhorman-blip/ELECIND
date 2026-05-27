<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de firma — {{ $tokenFirma->firmable->numero }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
@php
    $empresa  = \App\Models\Empresa::actual();
    $albaran  = $tokenFirma->firmable;
    $logoUrl  = $empresa->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($empresa->logo_path) : null;
    $color    = $empresa->color_primario ?? '#334155';
@endphp

<div style="max-width:640px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;">

    {{-- ══ CABECERA DOCUMENTO ══ --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:2px solid {{ $color }};padding:16px 20px;">
        <tr>
            {{-- Izquierda: logo + datos empresa --}}
            <td valign="top" style="width:60%;">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $empresa->nombre }}"
                         style="max-height:60px;max-width:180px;display:block;margin-bottom:8px;">
                @else
                    <div style="font-size:20px;font-weight:800;color:{{ $color }};margin-bottom:8px;">
                        {{ $empresa->nombre_comercial ?: $empresa->nombre }}
                    </div>
                @endif
                <div style="font-size:11px;color:#475569;line-height:1.6;">
                    @if ($empresa->nombre_comercial && $empresa->nombre)
                        <div style="font-weight:600;">{{ $empresa->nombre }}</div>
                    @endif
                    @if ($empresa->direccion)
                        <div>{{ $empresa->direccion }}</div>
                    @endif
                    @if ($empresa->codigo_postal || $empresa->poblacion)
                        <div>{{ trim($empresa->codigo_postal . ' ' . $empresa->poblacion) }}{{ $empresa->provincia ? ' (' . $empresa->provincia . ')' : '' }}</div>
                    @endif
                    @if ($empresa->telefono)
                        <div>Tlf. {{ $empresa->telefono }}</div>
                    @endif
                    @if ($empresa->email_contacto)
                        <div>{{ $empresa->email_contacto }}</div>
                    @endif
                </div>
            </td>

            {{-- Derecha: número y fecha --}}
            <td valign="top" style="width:40%;text-align:right;">
                <table cellpadding="4" cellspacing="0" style="margin-left:auto;border:1px solid #e2e8f0;border-radius:4px;">
                    <tr>
                        <td style="font-size:11px;color:#64748b;white-space:nowrap;">Nº Albarán</td>
                        <td style="font-size:14px;font-weight:700;color:#1e293b;padding-left:10px;">{{ $albaran->numero }}</td>
                    </tr>
                    <tr style="border-top:1px solid #f1f5f9;">
                        <td style="font-size:11px;color:#64748b;">Fecha</td>
                        <td style="font-size:13px;font-weight:600;color:#1e293b;padding-left:10px;">{{ $albaran->fecha->format('d/m/Y') }}</td>
                    </tr>
                    <tr style="border-top:1px solid #f1f5f9;">
                        <td style="font-size:11px;color:#64748b;">Tipo jornada</td>
                        <td style="font-size:12px;color:#1e293b;padding-left:10px;">{{ $albaran->tipo_hora->etiqueta() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ CLIENTE / PROYECTO ══ --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:10px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
        <tr>
            <td style="font-size:12px;color:#475569;">
                <strong>Cliente:</strong>
                {{ $albaran->cliente?->nombre ?? '—' }}
                @if ($albaran->proyecto)
                    &nbsp;·&nbsp; <strong>Proyecto:</strong> {{ $albaran->proyecto->nombre }}
                @endif
                @if ($albaran->concepto)
                    &nbsp;·&nbsp; <strong>Concepto:</strong> {{ $albaran->concepto->nombre }}
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ TABLA DE TRABAJADORES ══ --}}
    @php $totalLineas = $albaran->lineasPersonal->count(); @endphp
    <table width="100%" cellpadding="0" cellspacing="0">
        {{-- Cabecera tabla --}}
        <thead>
            <tr style="background:{{ $color }};">
                <th style="padding:8px 12px;font-size:11px;font-weight:700;text-align:left;color:#ffffff;text-transform:uppercase;letter-spacing:.05em;">
                    Trabajo realizado
                </th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;text-align:left;color:#ffffff;text-transform:uppercase;letter-spacing:.05em;">
                    Nombre del trabajador
                </th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;text-align:center;color:#ffffff;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">
                    Horas<br>normales
                </th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;text-align:center;color:#ffffff;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">
                    Horas<br>extras
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($albaran->lineasPersonal as $i => $linea)
                <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f8fafc' }};border-top:1px solid #e2e8f0;">
                    @if ($i === 0)
                        <td rowspan="{{ $totalLineas }}" style="padding:9px 12px;font-size:13px;color:#475569;vertical-align:middle;border-right:1px solid #e2e8f0;">
                            {{ $albaran->concepto?->nombre ?? '—' }}
                        </td>
                    @endif
                    <td style="padding:9px 12px;font-size:13px;color:#1e293b;font-weight:500;">
                        {{ trim(($linea->trabajador->nombre ?? '') . ' ' . ($linea->trabajador->apellidos ?? '')) ?: '—' }}
                    </td>
                    <td style="padding:9px 12px;font-size:13px;color:#1e293b;text-align:center;font-weight:600;">
                        {{ number_format((float) $linea->horas, 2) }}
                    </td>
                    <td style="padding:9px 12px;font-size:13px;color:#475569;text-align:center;">
                        {{ number_format((float) $linea->horas_extra, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:12px;text-align:center;color:#94a3b8;font-size:12px;font-style:italic;">
                        Sin trabajadores registrados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══ OBSERVACIONES ══ --}}
    @if ($albaran->observaciones)
        <div style="padding:10px 20px;border-top:1px solid #e2e8f0;font-size:12px;color:#475569;">
            <strong>Observaciones:</strong> {{ $albaran->observaciones }}
        </div>
    @endif

    {{-- ══ BOTÓN FIRMAR ══ --}}
    <div style="padding:24px 20px;border-top:2px solid #e2e8f0;text-align:center;">
        <p style="margin:0 0 6px;font-size:13px;color:#475569;">
            Se solicita tu firma como <strong>{{ $tokenFirma->tipo_firmante->etiqueta() }}</strong>
            @if ($tokenFirma->nombre_destino)
                , {{ $tokenFirma->nombre_destino }}
            @endif
            .
        </p>
        <a href="{{ route('albaranes.firmar', ['token' => $tokenFirma->token]) }}"
           style="display:inline-block;margin:12px auto 4px;padding:12px 32px;background:{{ $color }};color:#ffffff;text-decoration:none;border-radius:5px;font-size:15px;font-weight:700;">
            Firmar ahora
        </a>
        <p style="margin:8px 0 0;font-size:11px;color:#94a3b8;">
            Enlace válido hasta el {{ $tokenFirma->caduca_at->format('d/m/Y') }}.
        </p>
        <p style="margin:4px 0 0;font-size:10px;color:#cbd5e1;word-break:break-all;">
            {{ route('albaranes.firmar', ['token' => $tokenFirma->token]) }}
        </p>
    </div>

    {{-- ══ PIE ══ --}}
    <div style="padding:10px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#94a3b8;">
        {{ $empresa->nombre_comercial ?: $empresa->nombre }} · Correo generado automáticamente, no respondas a este mensaje.
    </div>

</div>
</body>
</html>
