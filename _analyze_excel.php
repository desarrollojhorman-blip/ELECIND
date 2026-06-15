<?php
ini_set('memory_limit', '4G');
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$path = __DIR__.'/docs/excel/tarifas/01_SEGUIMIENTO HORAS 2026 (4).xlsx';

$hoja = $argv[1] ?? null;
$maxFilas = isset($argv[2]) ? (int) $argv[2] : 50;

if ($hoja === null) {
    echo "Uso: php _analyze_excel.php <nombre_hoja> [max_filas]\n";
    exit(1);
}

$reader = new Xlsx();
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$hoja]);

echo "=== Hoja: $hoja ===\n";
$spreadsheet = $reader->load($path);
$sheet = $spreadsheet->getActiveSheet();

$highestRow = $sheet->getHighestDataRow();
$highestCol = $sheet->getHighestDataColumn();
$highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

echo "Rango: A1:{$highestCol}{$highestRow} ({$highestColIdx} cols × {$highestRow} filas)\n\n";

$filasAMostrar = min($highestRow, $maxFilas);

// Helper para sacar el valor cacheado sin calcular formulas
$leer = function($cell) {
    $val = $cell->getValue();
    // Si es Formula y hay un OldCalculatedValue, usar ese
    if ($cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) {
        $cached = $cell->getOldCalculatedValue();
        return $cached !== null ? $cached : '['.mb_substr($val, 0, 30).'...]';
    }
    return $val;
};

// Calcular anchos
$anchos = array_fill(0, $highestColIdx, 3);
for ($r = 1; $r <= $filasAMostrar; $r++) {
    for ($c = 1; $c <= $highestColIdx; $c++) {
        $val = $leer($sheet->getCellByColumnAndRow($c, $r));
        $strLen = mb_strlen((string) $val);
        if ($strLen > $anchos[$c - 1]) {
            $anchos[$c - 1] = min($strLen, 35);
        }
    }
}

// Imprimir filas
for ($r = 1; $r <= $filasAMostrar; $r++) {
    $fila = [];
    for ($c = 1; $c <= $highestColIdx; $c++) {
        $val = $leer($sheet->getCellByColumnAndRow($c, $r));
        $val = mb_substr((string) $val, 0, 35);
        $fila[] = str_pad($val, $anchos[$c - 1]);
    }
    echo sprintf("[%4d] %s\n", $r, implode(' | ', $fila));
}

if ($highestRow > $maxFilas) {
    echo "\n... (".($highestRow - $maxFilas)." filas más, sin mostrar)\n";
}
