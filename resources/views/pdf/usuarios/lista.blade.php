@php
    $esH      = $orientacion === 'horizontal';
    $fzBody   = $esH ? '7.5pt' : '6.5pt';
    $fzTabla  = $esH ? '7pt'   : '6pt';
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

    .badge-si { color: #16a34a; font-weight: bold; }
    .badge-no { color: #dc2626; }
    .id-cell  { font-family: monospace; color: #64748b; }
</style>
<?php /* Estilos dinámicos (colores de empresa y tamaños según orientación) */ ?>
<style>
    body            { font-size: <?= $fzBody ?>; }
    .empresa-nombre { color: <?= $colorPrimario ?>; }
    .titulo         { color: <?= $colorPrimario ?>; }
    table.datos thead th {
        background-color: <?= $colorPrimario ?>;
        color: <?= $colorTextoEncabezado ?>;
        font-size: <?= $fzTabla ?>;
    }
    table.datos tbody tr:nth-child(even) td {
        background-color: <?= $colorSecundario ?>;
    }
    table.datos tbody td { font-size: <?= $fzTabla ?>; }
</style>
</head>
<body>

<htmlpagefooter name="pie">
    <table style="width:100%; border-top:0.3mm solid #e2e8f0; padding-top:1.5mm;">
        <tr>
            <td style="font-size:6.5pt; color:#94a3b8; text-align:left;">
                <?= e($empresaNombre) ?> · Listado de Usuarios
            </td>
            <td style="font-size:6.5pt; color:#94a3b8; text-align:right;">
                Página {PAGENO} de {nbpg}
            </td>
        </tr>
    </table>
</htmlpagefooter>
<sethtmlpagefooter name="pie" value="on" />

{{-- Cabecera: logo izquierda, empresa + fecha derecha --}}
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

<div class="titulo">Listado de Usuarios</div>
<div class="subtitulo-info">
    Total: {{ $total }} {{ $total === 1 ? 'usuario' : 'usuarios' }}
    @if($filtrosActivos) &nbsp;·&nbsp; Filtros: {{ $filtrosActivos }} @endif
</div>

<table class="datos">
    <thead>
        <tr>
            <th style="width:4%">ID</th>
            <th style="width:{{ $esH ? '10%' : '9%' }}">Usuario</th>
            <th style="width:{{ $esH ? '11%' : '11%' }}">Nombre</th>
            <th style="width:{{ $esH ? '11%' : '11%' }}">Apellidos</th>
            <th style="width:{{ $esH ? '12%' : '11%' }}">Email</th>
            <th style="width:7%">DNI</th>
            <th style="width:8%">Teléfono</th>
            <th style="width:7%">Nº empl.</th>
            <th style="width:6%">Tipo</th>
            <th style="width:8%">Rol</th>
            <th style="width:{{ $esH ? '11%' : '11%' }}">Empresa</th>
            <th style="width:4%">Act.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($usuarios as $usuario)
            <tr>
                <td class="id-cell">{{ $usuario->id }}</td>
                <td>{{ $usuario->username }}</td>
                <td>{{ $usuario->nombre }}</td>
                <td>{{ $usuario->apellidos }}</td>
                <td>{{ $usuario->email }}</td>
                <td>{{ $usuario->dni }}</td>
                <td>{{ $usuario->telefono }}</td>
                <td>{{ $usuario->numero_empleado }}</td>
                <td>{{ ucfirst((string) $usuario->tipo_usuario) }}</td>
                <td>{{ $usuario->roles->pluck('name')->join(', ') }}</td>
                <td>{{ $usuario->cliente?->nombre }}</td>
                <td>
                    @if($usuario->activo)
                        <span class="badge-si">Sí</span>
                    @else
                        <span class="badge-no">No</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" style="text-align:center; color:#94a3b8; padding:5mm;">
                    No hay usuarios con los filtros aplicados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
