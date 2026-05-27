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
    $cli      = $albaran->cliente;
    $cliPoblacion = $cli
        ? trim(($cli->codigo_postal ? $cli->codigo_postal.' ' : '').($cli->poblacion ?? '')).
          ($cli->provincia ? ' ('.$cli->provincia.')' : '')
        : '';
    $totalLineas = $albaran->lineasPersonal->count();
    $preheader = 'Solicitud de firma del albarán '.$albaran->numero.' del '.$albaran->fecha->format('d/m/Y').'.';
@endphp

{{-- Preheader visible en la lista de la bandeja (snippet) Y único por envío para evitar que Gmail colapse el cuerpo --}}
<div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#ffffff;opacity:0;">
    {{ $preheader }} · Ref. {{ $tokenFirma->token }} · Enviado {{ now()->format('d/m/Y H:i:s') }}
    &#847; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp; &zwnj; &nbsp;
</div>

{{-- Tabla envolvente para centrado fiable en clientes de correo --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;">
    <tr>
        <td align="center" style="padding:24px 12px;">

            <table role="presentation" width="680" cellpadding="0" cellspacing="0" border="0" style="width:680px;max-width:680px;background:#ffffff;">
                <tr>
                    <td style="padding:24px;">

                        {{-- ══ CABECERA: logo + datos empresa | Nº/Fecha/Tipo jornada ══ --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="top" style="width:60%;padding-bottom:10px;">
                                    @if ($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="{{ $empresa->nombre }}"
                                             style="max-height:72px;max-width:220px;display:block;margin-bottom:6px;border:0;">
                                    @else
                                        <div style="font-size:18px;font-weight:800;color:#1f2937;margin-bottom:5px;">{{ $empresa->nombre }}</div>
                                    @endif
                                    <div style="font-size:10px;color:#475569;line-height:1.6;">
                                        @if ($empresa->razon_social)
                                            <div style="font-weight:600;">{{ $empresa->razon_social }}</div>
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
                                <td valign="top" style="width:40%;text-align:right;padding-bottom:10px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-left:auto;border:1px solid #e2e8f0;border-collapse:collapse;">
                                        <tr>
                                            <td style="padding:4px 8px;font-size:10px;color:#64748b;white-space:nowrap;">Nº Albarán</td>
                                            <td style="padding:4px 10px;font-size:13px;font-weight:700;color:#1e293b;">{{ $albaran->numero }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:4px 8px;font-size:10px;color:#64748b;border-top:1px solid #f1f5f9;">Fecha</td>
                                            <td style="padding:4px 10px;font-size:11px;font-weight:600;color:#1e293b;border-top:1px solid #f1f5f9;">{{ $albaran->fecha->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:4px 8px;font-size:10px;color:#64748b;border-top:1px solid #f1f5f9;">Tipo jornada</td>
                                            <td style="padding:4px 10px;font-size:11px;color:#1e293b;border-top:1px solid #f1f5f9;">{{ $albaran->tipo_hora->etiqueta() }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- ══ DATOS DEL CLIENTE ══ --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="padding:8px 0 4px 2px;font-size:9px;font-weight:800;color:#111827;text-transform:uppercase;letter-spacing:.06em;">
                                    Datos del cliente
                                </td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #d1d5db;padding:8px 12px;">
                                    <div style="font-size:10px;color:#1f2937;line-height:1.6;font-weight:700;">{{ $cli?->nombre ?? '—' }}</div>
                                    @if ($cli?->direccion)
                                        <div style="font-size:10px;color:#1f2937;line-height:1.6;">{{ $cli->direccion }}</div>
                                    @endif
                                    @if ($cliPoblacion !== '')
                                        <div style="font-size:10px;color:#1f2937;line-height:1.6;">{{ $cliPoblacion }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        {{-- ══ TABLA DE TRABAJADORES ══ --}}
                        @if ($albaran->lineasPersonal->isNotEmpty())
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #1f2937;border-collapse:collapse;margin-top:12px;">
                                <thead>
                                    <tr style="background:#1f2937;">
                                        <th align="left" style="padding:6px 10px;font-size:10px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:.04em;border-right:1px solid #475569;">Trabajo realizado</th>
                                        <th align="left" style="padding:6px 10px;font-size:10px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:.04em;border-right:1px solid #475569;">Nombre del trabajador</th>
                                        <th align="center" style="padding:6px 10px;font-size:10px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;border-right:1px solid #475569;">Horas normales</th>
                                        <th align="center" style="padding:6px 10px;font-size:10px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">Horas extras</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($albaran->lineasPersonal as $i => $linea)
                                        <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                            @if ($i === 0)
                                                <td rowspan="{{ $totalLineas }}" valign="middle" style="padding:7px 10px;font-size:11px;color:#475569;border-top:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">{{ $albaran->concepto?->nombre ?? '—' }}</td>
                                            @endif
                                            <td style="padding:7px 10px;font-size:11px;color:#1e293b;border-top:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                                                {{ trim(($linea->trabajador->nombre ?? '') . ' ' . ($linea->trabajador->apellidos ?? '')) ?: '—' }}
                                            </td>
                                            <td align="center" style="padding:7px 10px;font-size:11px;color:#1e293b;font-weight:600;border-top:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">{{ number_format((float) $linea->horas, 2) }}</td>
                                            <td align="center" style="padding:7px 10px;font-size:11px;color:#1e293b;border-top:1px solid #e2e8f0;">{{ number_format((float) $linea->horas_extra, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        {{-- ══ OBSERVACIONES ══ --}}
                        @if ($albaran->observaciones)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top:12px;font-size:10px;color:#475569;">
                                        <strong>Observaciones:</strong> {{ $albaran->observaciones }}
                                    </td>
                                </tr>
                            </table>
                        @endif

                        {{-- ══ BOTÓN FIRMAR ══ --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:30px;">
                            <tr>
                                <td align="center">
                                    <p style="margin:0 0 6px;font-size:13px;color:#475569;">
                                        Se solicita tu firma como <strong>{{ $tokenFirma->tipo_firmante->etiqueta() }}</strong>@if ($tokenFirma->nombre_destino), {{ $tokenFirma->nombre_destino }}@endif.
                                    </p>
                                    <a href="{{ route('albaranes.firmar', ['token' => $tokenFirma->token]) }}"
                                       style="display:inline-block;margin:12px auto 4px;padding:12px 32px;background:#1f2937;color:#ffffff;text-decoration:none;border-radius:5px;font-size:15px;font-weight:700;">
                                        Firmar ahora
                                    </a>
                                    <p style="margin:8px 0 0;font-size:11px;color:#94a3b8;">
                                        Enlace válido hasta el {{ $tokenFirma->caduca_at->format('d/m/Y') }}.
                                    </p>
                                    <p style="margin:4px 0 0;font-size:10px;color:#cbd5e1;word-break:break-all;">
                                        {{ route('albaranes.firmar', ['token' => $tokenFirma->token]) }}
                                    </p>
                                </td>
                            </tr>
                        </table>

                        {{-- ══ PIE ══ --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;border-top:1px solid #e2e8f0;">
                            <tr>
                                <td align="center" style="padding-top:10px;font-size:10px;color:#94a3b8;">
                                    {{ $empresa->nombre }} · Correo generado automáticamente, no respondas a este mensaje.
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>
