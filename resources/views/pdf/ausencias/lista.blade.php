@php
    $esH    = $orientacion === 'horizontal';
    $fzBody = $esH ? '8pt'  : '7.5pt';
    $fzTbl  = $esH ? '7.5pt' : '7pt';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 15mm 10mm 20mm 10mm; }

    body {
        font-family: DejaVu Sans, sans-serif;
        color: #1e293b;
    }

    table.header { width: 100%; margin-bottom: 5mm; }
    table.header td.logo-cell { vertical-align: middle; }
    table.header td.logo-cell img { max-height: 12mm; max-width: 50mm; }

    .empresa-nombre { font-size: 10pt; font-weight: bold; }

    .titulo         { font-size: 12pt; font-weight: bold; margin-bottom: 1mm; }
    .subtitulo-info { font-size: 7pt; color: #64748b; margin-bottom: 3mm; }

    table.datos { width: 100%; border-collapse: collapse; }
    table.datos thead th {
        font-weight: bold;
        padding: 2mm 1.5mm;
        text-align: left;
        border: none;
    }
    table.datos tbody td {
        padding: 1.5mm 1.5mm;
        border-bottom: 0.2mm solid #e2e8f0;
        vertical-align: top;
    }

    .mono     { font-family: DejaVu Sans Mono, monospace; }
    .muted    { color: #64748b; }

    .estado-pendiente  { color: #d97706; font-weight: bold; }
    .estado-aprobada   { color: #16a34a; font-weight: bold; }
    .estado-rechazada  { color: #dc2626; font-weight: bold; }
    .estado-cancelada  { color: #6b7280; }
</style>
<style>
    body            { font-size: <?= $fzBody ?>; }
    .empresa-nombre { color: <?= $colorPrimario ?>; }
    .titulo         { color: <?= $colorPrimario ?>; }
    table.datos thead th {
        background-color: <?= $colorPrimario ?>;
        color: <?= $colorTextoEncabezado ?>;
        font-size: <?= $fzTbl ?>;
    }
    table.datos tbody tr:nth-child(even) td {
        background-color: <?= $colorSecundario ?>;
    }
    table.datos tbody td { font-size: <?= $fzTbl ?>; }
</style>
</head>
<body>

<htmlpagefooter name="pie">
    <table style="width:100%; border-top:0.3mm solid #e2e8f0; padding-top:1.5mm;">
        <tr>
            <td style="font-size:6.5pt; color:#94a3b8; text-align:left;">
                <?= e($empresaNombre) ?> · Listado de Ausencias
            </td>
            <td style="font-size:6.5pt; color:#94a3b8; text-align:right;">
                Página {PAGENO} de {nbpg}
            </td>
        </tr>
    </table>
</htmlpagefooter>
<sethtmlpagefooter name="pie" value="on" />

<table class="header">
    <tr>
        <td class="logo-cell">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $empresaNombre }}">
            @else
                <span class="empresa-nombre">{{ $empresaNombre }}</span>
            @endif
        </td>
    </tr>
</table>

<div class="titulo">Listado de Ausencias</div>
<div class="subtitulo-info">
    Total: {{ $total }} {{ $total === 1 ? 'ausencia' : 'ausencias' }}
    &nbsp;·&nbsp; {{ $fecha }}
    @if($filtrosActivos) &nbsp;·&nbsp; Filtros: {{ $filtrosActivos }} @endif
</div>

<table class="datos">
    <thead>
        <tr>
            <th style="width:4%">ID</th>
            <th style="width:{{ $esH ? '22%' : '20%' }}">Trabajador</th>
            <th style="width:{{ $esH ? '12%' : '11%' }}">Tipo</th>
            <th style="width:9%">F. Inicio</th>
            <th style="width:9%">F. Fin</th>
            <th style="width:4%; text-align:center;">Días</th>
            <th style="width:9%">Estado</th>
            <th style="width:{{ $esH ? '18%' : '16%' }}">Aprobado por</th>
            <th style="width:{{ $esH ? '13%' : '18%' }}">Motivo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($ausencias as $ausencia)
            @php
                $estadoClass = match($ausencia->estado->value) {
                    'aprobada'  => 'estado-aprobada',
                    'rechazada' => 'estado-rechazada',
                    'cancelada' => 'estado-cancelada',
                    default     => 'estado-pendiente',
                };
            @endphp
            <tr>
                <td class="mono muted">{{ $ausencia->id }}</td>
                <td>
                    {{ trim($ausencia->trabajador?->apellidos . ' ' . $ausencia->trabajador?->nombre) ?: '—' }}
                    @if($ausencia->trabajador?->numero_empleado)
                        <br><span class="muted">{{ $ausencia->trabajador->numero_empleado }}</span>
                    @endif
                </td>
                <td>{{ $ausencia->tipo->etiqueta() }}</td>
                <td class="mono">{{ $ausencia->fecha_inicio->format('d/m/Y') }}</td>
                <td class="mono">{{ $ausencia->fecha_fin->format('d/m/Y') }}</td>
                <td style="text-align:center;">{{ $ausencia->diasNaturales() }}</td>
                <td class="{{ $estadoClass }}">{{ $ausencia->estado->etiqueta() }}</td>
                <td>
                    @if($ausencia->aprobador)
                        {{ trim($ausencia->aprobador->apellidos . ' ' . $ausencia->aprobador->nombre) }}
                        @if($ausencia->aprobado_at)
                            <br><span class="muted">{{ $ausencia->aprobado_at->format('d/m/Y') }}</span>
                        @endif
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td class="muted">{{ $ausencia->motivo ?: '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; color:#94a3b8; padding:5mm;">
                    No hay ausencias con los filtros aplicados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
