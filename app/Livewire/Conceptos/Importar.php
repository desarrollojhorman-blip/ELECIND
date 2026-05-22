<?php

namespace App\Livewire\Conceptos;

use App\Models\Concepto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Layout('components.layouts.web', ['active' => 'conceptos'])]
#[Title('Importar conceptos')]
class Importar extends Component
{
    use WithFileUploads;

    public const MAX_FILE_MB = 10;

    public const MAX_FILAS = 5000;

    public ?TemporaryUploadedFile $archivo = null;

    public bool $tieneEncabezados = true;

    public int $filaInicio = 1;

    public bool $procesado = false;

    /** @var array<int, array{indice:int, titulo:string}> */
    public array $columnas = [];

    /** @var array<int, array<int, string>> */
    public array $muestras = [];

    /** @var array<int, string> indiceColumna => campo de Concepto ('' = no usar) */
    public array $mapeo = [];

    public int $totalFilas = 0;

    /** @var array<int, array{fila:int, columna:string, motivo:string}> */
    public array $errores = [];

    public function mount(): void
    {
        abort_unless(Gate::allows('conceptos.importar'), 403);
    }

    /** @return array<string, string> */
    public function camposDisponibles(): array
    {
        return [
            'nombre' => 'Nombre  (obligatorio)',
            'descripcion' => 'Descripción',
            'activo' => 'Activo (sí/no)',
        ];
    }

    /** @return array<string, array<int, string>> */
    private function aliasCampos(): array
    {
        return [
            'nombre' => ['nombre', 'concepto', 'denominacion', 'titulo'],
            'descripcion' => ['descripcion', 'desc', 'detalle', 'notas', 'observaciones'],
            'activo' => ['activo', 'estado', 'active', 'alta'],
        ];
    }

    private function normalizar(string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim($texto)));
        $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto) ?? '';

        return trim($texto);
    }

    /** @return array<int, array<int, string|null>> */
    private function leerHoja(): array
    {
        $ruta = $this->archivo->getRealPath();
        $hoja = IOFactory::load($ruta)->getActiveSheet();

        /** @var array<int, array<int, string|null>> $filas */
        $filas = $hoja->toArray(null, true, true, false);

        return $filas;
    }

    public function procesarArchivo(): void
    {
        $this->resetErrorBag();
        $this->errores = [];

        $this->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:'.(self::MAX_FILE_MB * 1024)],
            'filaInicio' => ['integer', 'min:1', 'max:1000'],
        ], [
            'archivo.max' => 'El archivo no puede superar '.self::MAX_FILE_MB.' MB.',
            'archivo.mimes' => 'Formato no válido. Sube un archivo .xlsx, .xls o .csv.',
        ], [
            'archivo' => 'archivo',
            'filaInicio' => 'fila de inicio',
        ]);

        $filas = $this->leerHoja();
        $offset = max(0, $this->filaInicio - 1);
        $filas = array_slice($filas, $offset);

        if ($filas === []) {
            $this->addError('archivo', 'El archivo no contiene datos a partir de la fila indicada.');

            return;
        }

        $numCols = 0;
        foreach ($filas as $fila) {
            $numCols = max($numCols, count($fila));
        }

        $filasDatos = $filas;
        $titulos = [];

        if ($this->tieneEncabezados) {
            $cabecera = array_shift($filasDatos) ?? [];
            for ($c = 0; $c < $numCols; $c++) {
                $valor = isset($cabecera[$c]) ? trim((string) $cabecera[$c]) : '';
                $titulos[$c] = $valor !== '' ? $valor : 'Columna '.$this->letraColumna($c);
            }
        } else {
            for ($c = 0; $c < $numCols; $c++) {
                $titulos[$c] = 'Columna '.$this->letraColumna($c);
            }
        }

        $filasDatos = array_values(array_filter($filasDatos, function ($fila): bool {
            foreach ($fila as $celda) {
                if (trim((string) $celda) !== '') {
                    return true;
                }
            }

            return false;
        }));

        $this->columnas = [];
        $this->muestras = [];
        $this->mapeo = [];

        $aliases = $this->aliasCampos();

        foreach ($titulos as $indice => $titulo) {
            $this->columnas[] = ['indice' => $indice, 'titulo' => $titulo];

            $muestra = [];
            foreach ($filasDatos as $fila) {
                $val = isset($fila[$indice]) ? trim((string) $fila[$indice]) : '';
                $muestra[] = $val;
                if (count($muestra) >= 3) {
                    break;
                }
            }
            $this->muestras[$indice] = $muestra;

            $this->mapeo[$indice] = '';
            if ($this->tieneEncabezados) {
                $norm = $this->normalizar($titulo);
                foreach ($aliases as $campo => $listaAlias) {
                    if (in_array($norm, $listaAlias, true)) {
                        if (! in_array($campo, $this->mapeo, true)) {
                            $this->mapeo[$indice] = $campo;
                        }
                        break;
                    }
                }
            }
        }

        $numFilasDatos = count($filasDatos);
        if ($numFilasDatos > self::MAX_FILAS) {
            $this->addError('archivo', 'El archivo tiene '.number_format($numFilasDatos, 0, ',', '.').' filas. Máximo: '.number_format(self::MAX_FILAS, 0, ',', '.').'.');

            return;
        }

        $this->totalFilas = $numFilasDatos;
        $this->procesado = true;
    }

    private function letraColumna(int $indice): string
    {
        $letra = '';
        $indice++;
        while ($indice > 0) {
            $mod = ($indice - 1) % 26;
            $letra = chr(65 + $mod).$letra;
            $indice = intdiv($indice - $mod, 26);
        }

        return $letra;
    }

    /** @return array<int, string> */
    private function camposDuplicados(): array
    {
        $usados = array_filter($this->mapeo, fn ($c): bool => $c !== '');
        $conteo = array_count_values($usados);

        return array_keys(array_filter($conteo, fn ($n): bool => $n > 1));
    }

    private function interpretarActivo(string $valor): bool
    {
        $v = $this->normalizar($valor);
        if ($v === '') {
            return true;
        }

        return in_array($v, ['1', 'si', 'sí', 's', 'true', 'x', 'y', 'yes', 'activo', 'verdadero', 'alta'], true);
    }

    public function importar(): void
    {
        abort_unless(Gate::allows('conceptos.importar'), 403);
        $this->resetErrorBag();
        $this->errores = [];

        if (! $this->procesado || $this->archivo === null) {
            $this->addError('archivo', 'Vuelve a subir el archivo para continuar.');

            return;
        }

        $duplicados = $this->camposDuplicados();
        if ($duplicados !== []) {
            $labels = array_map(fn ($c) => $this->camposDisponibles()[$c] ?? $c, $duplicados);
            $this->addError('mapeo', 'Hay campos asignados a más de una columna: '.implode(', ', $labels).'. Cada campo solo puede usarse una vez.');

            return;
        }

        $destinos = array_filter($this->mapeo, fn ($c): bool => $c !== '');

        if (! in_array('nombre', $destinos, true)) {
            $this->addError('mapeo', 'Debes asignar una columna al campo «Nombre» (es obligatorio).');

            return;
        }

        $filas = $this->leerHoja();
        $filas = array_slice($filas, max(0, $this->filaInicio - 1), null, true);

        $tieneCab = $this->tieneEncabezados;
        $filaBaseHoja = $this->filaInicio + ($tieneCab ? 1 : 0);

        $indices = array_keys($filas);
        if ($tieneCab && $indices !== []) {
            array_shift($indices);
        }

        // Nombres ya existentes en BD (no-trashed) — comparación case-insensitive.
        $nombresBD = Concepto::query()
            ->pluck('nombre')
            ->map(fn ($v) => mb_strtolower(trim((string) $v)))
            ->all();

        $nombresVistos = [];

        /** @var array<int, array<string, mixed>> $aCrear */
        $aCrear = [];
        $erroresLocal = [];
        $numeroLinea = $filaBaseHoja;

        foreach ($indices as $i) {
            $fila = $filas[$i];

            $vacia = true;
            foreach ($fila as $celda) {
                if (trim((string) $celda) !== '') {
                    $vacia = false;
                    break;
                }
            }
            if ($vacia) {
                $numeroLinea++;

                continue;
            }

            $datos = [];
            foreach ($destinos as $colIndice => $campo) {
                $valor = isset($fila[$colIndice]) ? trim((string) $fila[$colIndice]) : '';
                $datos[$campo] = $valor;
            }

            $datos['activo'] = in_array('activo', $destinos, true)
                ? $this->interpretarActivo($datos['activo'] ?? '')
                : true;

            if (isset($datos['descripcion']) && $datos['descripcion'] === '') {
                $datos['descripcion'] = null;
            }

            $reglas = [
                'nombre' => ['required', 'string', 'max:120'],
                'descripcion' => ['nullable', 'string', 'max:1000'],
                'activo' => ['boolean'],
            ];

            $mensajes = [
                'required' => 'El campo :attribute es obligatorio.',
                'string' => 'El campo :attribute debe ser texto.',
                'max' => 'El campo :attribute no puede superar :max caracteres.',
                'boolean' => 'El campo :attribute no es válido.',
            ];

            $validador = Validator::make($datos, $reglas, $mensajes, [
                'nombre' => 'Nombre',
                'descripcion' => 'Descripción',
                'activo' => 'Activo',
            ]);

            if ($validador->fails()) {
                foreach ($validador->errors()->messages() as $campo => $listaMsgs) {
                    $erroresLocal[] = [
                        'fila' => $numeroLinea,
                        'columna' => $this->camposDisponibles()[$campo] ?? $campo,
                        'motivo' => $listaMsgs[0],
                    ];
                }
                $numeroLinea++;

                continue;
            }

            // Unicidad del nombre: BD + dentro del propio archivo.
            $nombre = (string) ($datos['nombre'] ?? '');
            $norm = mb_strtolower(trim($nombre));
            if (in_array($norm, $nombresBD, true)) {
                $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Nombre', 'motivo' => "«{$nombre}» ya existe en la base de datos."];
            } elseif (in_array($norm, $nombresVistos, true)) {
                $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Nombre', 'motivo' => "«{$nombre}» está repetido dentro del archivo."];
            } else {
                $nombresVistos[] = $norm;
            }

            $aCrear[] = $datos;
            $numeroLinea++;
        }

        if ($aCrear === [] && $erroresLocal === []) {
            $this->addError('archivo', 'No se ha encontrado ninguna fila con datos para importar.');

            return;
        }

        // Regla de oro: un solo error → no se guarda NADA.
        if ($erroresLocal !== []) {
            usort($erroresLocal, fn ($a, $b): int => $a['fila'] <=> $b['fila']);
            $this->errores = $erroresLocal;

            return;
        }

        DB::transaction(function () use ($aCrear): void {
            foreach ($aCrear as $datos) {
                Concepto::create($datos);
            }
        });

        session()->flash('status', count($aCrear).' '.(count($aCrear) === 1 ? 'concepto importado' : 'conceptos importados').' correctamente.');
        $this->redirectRoute('conceptos.index', navigate: true);
    }

    public function reiniciar(): void
    {
        $this->reset(['archivo', 'procesado', 'columnas', 'muestras', 'mapeo', 'totalFilas', 'errores']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.conceptos.importar', [
            'maxMb' => self::MAX_FILE_MB,
            'maxFilas' => self::MAX_FILAS,
        ]);
    }
}
