<?php
ini_set('memory_limit', '4G');
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$path = __DIR__.'/docs/excel/tarifas/01_SEGUIMIENTO HORAS 2026 (4).xlsx';

$reader = new Xlsx();
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly(['TARIFAS']);

$ss = $reader->load($path);
$sheet = $ss->getActiveSheet();

$highestRow = $sheet->getHighestDataRow();

$leer = function($cell) {
    $v = $cell->getValue();
    if ($cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) {
        return $cell->getOldCalculatedValue() ?? '?';
    }
    return $v;
};

// Cabeceras en fila 3: D=Nº CLIENTE, E=CLIENTE, F=PROYECTO, G=TIPO PROYECTO (denominación), H=TIPO HORA (key), I=TARIFA
// Datos empiezan en fila 4

$clientes = [];
$tiposHoraUnicos = [];
$proyectos = [];
$tarifasPorProyecto = []; // key: cliente|proyecto|denom => [tipo_hora => tarifa]

for ($r = 4; $r <= $highestRow; $r++) {
    $nClie = $leer($sheet->getCellByColumnAndRow(4, $r));
    $cliente = $leer($sheet->getCellByColumnAndRow(5, $r));
    $proyecto = $leer($sheet->getCellByColumnAndRow(6, $r));
    $denom = $leer($sheet->getCellByColumnAndRow(7, $r));
    $tipoHoraKey = $leer($sheet->getCellByColumnAndRow(8, $r));
    $tarifa = $leer($sheet->getCellByColumnAndRow(9, $r));

    if ($nClie === null || $proyecto === null) continue;

    // Extraer tipo_hora de la clave compuesta {proyecto}-{tipo}
    $tipoHora = null;
    if (is_string($tipoHoraKey) && str_starts_with($tipoHoraKey, $proyecto.'-')) {
        $tipoHora = substr($tipoHoraKey, strlen($proyecto) + 1);
    } else {
        // Caso especial: PLUS RETEN tipo "2-PLUS RETEN" (Nº cliente como prefijo)
        $partes = explode('-', $tipoHoraKey, 2);
        $tipoHora = end($partes);
    }

    $clientes[$nClie] = $cliente;
    $tiposHoraUnicos[$tipoHora] = ($tiposHoraUnicos[$tipoHora] ?? 0) + 1;
    $proyectos[$proyecto] = ['cliente' => $cliente, 'denom' => $denom, 'nClie' => $nClie];
    $tarifasPorProyecto[$proyecto][$tipoHora] = $tarifa;
}

echo "=== RESUMEN TARIFAS ===\n\n";

echo "## Clientes en TARIFAS (".count($clientes).")\n";
ksort($clientes);
foreach ($clientes as $nClie => $nombre) {
    echo " - $nClie: $nombre\n";
}

echo "\n## Tipos de hora únicos (".count($tiposHoraUnicos).")\n";
arsort($tiposHoraUnicos);
foreach ($tiposHoraUnicos as $t => $c) {
    echo "  - $t ($c apariciones)\n";
}

echo "\n## Proyectos con tarifas (".count($proyectos).")\n";
ksort($proyectos);
foreach ($proyectos as $cod => $info) {
    echo sprintf(" - %-15s | %s | %s\n", $cod, $info['denom'], $info['cliente']);
}

echo "\n## Tarifas por proyecto (resumido)\n";
foreach ($proyectos as $cod => $info) {
    $tarifas = $tarifasPorProyecto[$cod] ?? [];
    if (empty($tarifas)) continue;

    // Agrupar tarifas iguales
    $grupos = [];
    foreach ($tarifas as $tipo => $val) {
        $grupos[(string) $val][] = $tipo;
    }

    echo sprintf("\n  %s (%s · %s):\n", $cod, $info['denom'], $info['cliente']);
    foreach ($grupos as $val => $tipos) {
        echo sprintf("    %s €  → %s\n", $val, implode(', ', $tipos));
    }
}
