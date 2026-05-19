<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use App\Services\NumeracionService;
use App\Support\ClienteFields;
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

#[Layout('components.layouts.web', ['active' => 'clientes'])]
#[Title('Importar clientes')]
class Importar extends Component
{
    use WithFileUploads;

    /** Tamaño máximo del archivo subido, en MB. */
    public const MAX_FILE_MB = 15;

    /** Tope de filas de datos por importación (lectura síncrona en memoria). */
    public const MAX_FILAS = 5000;

    public ?TemporaryUploadedFile $archivo = null;

    public bool $tieneEncabezados = true;

    /** Fila (1-indexada de la hoja) donde empieza el contenido. */
    public int $filaInicio = 1;

    public bool $procesado = false;

    /** @var array<int, array{indice:int, titulo:string}> */
    public array $columnas = [];

    /** @var array<int, array<int, string>> Hasta 3 valores de muestra por columna */
    public array $muestras = [];

    /** @var array<int, string> indiceColumna => campo Cliente ('' = no usar) */
    public array $mapeo = [];

    public int $totalFilas = 0;

    /** @var array<int, array{fila:int, columna:string, motivo:string}> */
    public array $errores = [];

    public ?int $importados = null;

    public function mount(): void
    {
        abort_unless(Gate::allows('clientes.importar'), 403);
    }

    /**
     * Campos de Cliente a los que se puede mapear una columna del archivo.
     *
     * @return array<string, string>
     */
    public function camposDisponibles(): array
    {
        return [
            'codigo_cliente' => 'Código de cliente',
            'nombre' => 'Nombre / Razón social  (obligatorio)',
            'nombre_comercial' => 'Nombre comercial',
            'cif' => 'CIF / NIF',
            'direccion' => 'Dirección',
            'codigo_postal' => 'Código postal',
            'poblacion' => 'Población',
            'provincia' => 'Provincia',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'activo' => 'Activo (sí/no)',
            'observaciones' => 'Observaciones',
        ];
    }

    /**
     * Alias de encabezados para auto-sugerir el mapeo.
     *
     * @return array<string, array<int, string>>
     */
    private function aliasCampos(): array
    {
        return [
            'codigo_cliente' => ['codigo', 'codigo cliente', 'cod cliente', 'numero cliente', 'n cliente', 'nº cliente', 'cliente codigo', 'id cliente'],
            'nombre' => ['nombre', 'razon social', 'razon', 'cliente', 'empresa', 'denominacion'],
            'nombre_comercial' => ['nombre comercial', 'comercial', 'alias', 'rotulo'],
            'cif' => ['cif', 'nif', 'dni', 'cif nif', 'nif cif', 'documento'],
            'direccion' => ['direccion', 'domicilio', 'calle', 'dir'],
            'codigo_postal' => ['codigo postal', 'cp', 'cod postal', 'postal'],
            'poblacion' => ['poblacion', 'ciudad', 'localidad', 'municipio'],
            'provincia' => ['provincia', 'prov'],
            'telefono' => ['telefono', 'tlf', 'tel', 'movil', 'phone', 'contacto'],
            'email' => ['email', 'correo', 'e mail', 'mail', 'correo electronico'],
            'activo' => ['activo', 'estado', 'active', 'alta'],
            'observaciones' => ['observaciones', 'notas', 'obs', 'comentarios', 'comentario'],
        ];
    }

    private function normalizar(string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim($texto)));
        $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto) ?? '';

        return trim($texto);
    }

    /**
     * Lee la hoja del archivo subido y devuelve sus filas como array 2D.
     *
     * @return array<int, array<int, string|null>>
     */
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
        $this->importados = null;

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

        // Saltamos las filas previas a "filaInicio" (1-indexada).
        $offset = max(0, $this->filaInicio - 1);
        $filas = array_slice($filas, $offset);

        if ($filas === []) {
            $this->addError('archivo', 'El archivo no contiene datos a partir de la fila indicada.');

            return;
        }

        // Determinar nº de columnas (la fila más ancha).
        $numCols = 0;
        foreach ($filas as $fila) {
            $numCols = max($numCols, count($fila));
        }

        // Encabezados
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

        // Filtrar filas totalmente vacías
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

            // Muestra: hasta 3 primeros valores no vacíos de esa columna.
            $muestra = [];
            foreach ($filasDatos as $fila) {
                $val = isset($fila[$indice]) ? trim((string) $fila[$indice]) : '';
                $muestra[] = $val;
                if (count($muestra) >= 3) {
                    break;
                }
            }
            $this->muestras[$indice] = $muestra;

            // Auto-sugerencia de mapeo por nombre de encabezado.
            $this->mapeo[$indice] = '';
            if ($this->tieneEncabezados) {
                $norm = $this->normalizar($titulo);
                foreach ($aliases as $campo => $listaAlias) {
                    if (in_array($norm, $listaAlias, true)) {
                        // No repetir un campo ya asignado a otra columna.
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
            $this->addError('archivo', 'El archivo tiene '.number_format($numFilasDatos, 0, ',', '.').' filas de datos. El máximo por importación es '.number_format(self::MAX_FILAS, 0, ',', '.').'. Divide el archivo en varias partes.');

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

    /** Campos mapeados más de una vez (no permitido). @return array<int, string> */
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
        abort_unless(Gate::allows('clientes.importar'), 403);
        $this->resetErrorBag();
        $this->errores = [];
        $this->importados = null;

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
            $this->addError('mapeo', 'Debes asignar una columna al campo «Nombre / Razón social» (es obligatorio).');

            return;
        }

        // Releer el archivo y reconstruir las filas de datos igual que en procesarArchivo().
        $filas = $this->leerHoja();
        $filas = array_slice($filas, max(0, $this->filaInicio - 1), null, true);

        $tieneCab = $this->tieneEncabezados;
        $filaBaseHoja = $this->filaInicio + ($tieneCab ? 1 : 0); // nº de línea real de la 1ª fila de datos

        $indices = array_keys($filas);
        if ($tieneCab && $indices !== []) {
            array_shift($indices);
        }

        if (count($indices) > self::MAX_FILAS) {
            $this->addError('archivo', 'El archivo supera el máximo de '.number_format(self::MAX_FILAS, 0, ',', '.').' filas por importación.');

            return;
        }

        $codigoMapeado = in_array('codigo_cliente', $destinos, true);

        // Campos con unicidad: la LISTA vive en ClienteFields (misma fuente que
        // el alta) → si allí cambia, aquí se entera solo. El "cómo" (precarga
        // de BD + duplicados dentro del propio archivo) es propio del lote y
        // vive aquí. Solo se comprueban los únicos que además estén mapeados
        // (el código sin mapear se autogenera, por eso no procede).
        $camposUnicos = array_values(array_intersect(ClienteFields::uniqueFields(), $destinos));

        /** @var array<string, array<int, string>> $valoresBD */
        $valoresBD = [];
        /** @var array<string, array<int, string>> $valoresVistos */
        $valoresVistos = [];
        foreach ($camposUnicos as $campo) {
            $valoresBD[$campo] = Cliente::query()
                ->whereNotNull($campo)->where($campo, '!=', '')
                ->pluck($campo)
                ->map(fn ($v) => mb_strtolower(trim((string) $v)))
                ->all();
            $valoresVistos[$campo] = [];
        }

        /** @var array<int, array<string, mixed>> $aCrear */
        $aCrear = [];
        $erroresLocal = [];
        $numeroLinea = $filaBaseHoja;

        foreach ($indices as $i) {
            $fila = $filas[$i];

            // Saltar filas totalmente vacías sin contarlas como error.
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

            // activo: por defecto true si no se mapea esa columna.
            if (in_array('activo', $destinos, true)) {
                $datos['activo'] = $this->interpretarActivo($datos['activo'] ?? '');
            } else {
                $datos['activo'] = true;
            }

            // Normalizar vacíos a null (salvo nombre que es obligatorio).
            foreach (['codigo_cliente', 'nombre_comercial', 'cif', 'direccion', 'codigo_postal', 'poblacion', 'provincia', 'telefono', 'email', 'observaciones'] as $campo) {
                if (isset($datos[$campo]) && $datos[$campo] === '') {
                    $datos[$campo] = null;
                }
            }

            // Validación de formato: MISMAS reglas que el formulario, desde la
            // fuente única de verdad (ClienteFields). Los `unique` dinámicos no
            // viven ahí; la unicidad se comprueba aparte, más abajo.
            $reglas = ClienteFields::getValidationRules();

            // Si no se mapeó "Código de cliente" se autogenera → no se valida aquí.
            if (! $codigoMapeado) {
                unset($reglas['codigo_cliente']);
            }

            $mensajes = [
                'required' => 'El campo :attribute es obligatorio.',
                'integer' => 'El campo :attribute debe ser un número entero.',
                'min' => 'El campo :attribute debe ser al menos :min.',
                'max' => 'El campo :attribute no puede superar :max.',
                'email' => 'El :attribute no tiene un formato válido.',
                'boolean' => 'El campo :attribute no es válido.',
            ];

            $validador = Validator::make($datos, $reglas, $mensajes, [
                'nombre' => 'Nombre',
                'codigo_cliente' => 'Código de cliente',
                'cif' => 'CIF',
                'email' => 'Email',
                'nombre_comercial' => 'Nombre comercial',
                'direccion' => 'Dirección',
                'codigo_postal' => 'Código postal',
                'poblacion' => 'Población',
                'provincia' => 'Provincia',
                'telefono' => 'Teléfono',
                'observaciones' => 'Observaciones',
            ]);

            if ($validador->fails()) {
                foreach ($validador->errors()->messages() as $campo => $mensajes) {
                    $erroresLocal[] = [
                        'fila' => $numeroLinea,
                        'columna' => $this->camposDisponibles()[$campo] ?? $campo,
                        'motivo' => $mensajes[0],
                    ];
                }
                $numeroLinea++;

                continue;
            }

            // Unicidad: solo los campos únicos mapeados (lista en ClienteFields).
            foreach ($camposUnicos as $campo) {
                $valor = $datos[$campo] ?? null;
                if ($valor === null || $valor === '') {
                    continue;
                }
                $norm = mb_strtolower(trim((string) $valor));
                $etiqueta = $this->camposDisponibles()[$campo] ?? $campo;
                if (in_array($norm, $valoresBD[$campo], true)) {
                    $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => $etiqueta, 'motivo' => "«{$valor}» ya existe en la base de datos."];
                } elseif (in_array($norm, $valoresVistos[$campo], true)) {
                    $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => $etiqueta, 'motivo' => "«{$valor}» está repetido dentro del archivo."];
                } else {
                    $valoresVistos[$campo][] = $norm;
                }
            }

            $aCrear[] = $datos;
            $numeroLinea++;
        }

        if ($aCrear === [] && $erroresLocal === []) {
            $this->addError('archivo', 'No se ha encontrado ninguna fila con datos para importar.');

            return;
        }

        // Regla de oro: si hay UN solo error, no se guarda NADA.
        if ($erroresLocal !== []) {
            usort($erroresLocal, fn ($a, $b): int => $a['fila'] <=> $b['fila']);
            $this->errores = $erroresLocal;

            return;
        }

        // Autogeneración de código si no se mapeó la columna.
        if (! $codigoMapeado) {
            $base = app(NumeracionService::class)->siguienteNumeroCliente();
            foreach ($aCrear as $idx => $datos) {
                $aCrear[$idx]['codigo_cliente'] = $base + $idx;
            }
        }

        DB::transaction(function () use ($aCrear): void {
            foreach ($aCrear as $datos) {
                Cliente::create($datos);
            }
        });

        session()->flash('status', count($aCrear).' '.(count($aCrear) === 1 ? 'cliente importado' : 'clientes importados').' correctamente.');
        $this->redirectRoute('clientes.index', navigate: true);
    }

    public function reiniciar(): void
    {
        $this->reset(['archivo', 'procesado', 'columnas', 'muestras', 'mapeo', 'totalFilas', 'errores', 'importados']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.clientes.importar', [
            'maxMb' => self::MAX_FILE_MB,
            'maxFilas' => self::MAX_FILAS,
        ]);
    }
}
