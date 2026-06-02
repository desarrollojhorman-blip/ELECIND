<?php

namespace App\Livewire\Usuarios;

use App\Models\Cliente;
use App\Models\Role;
use App\Models\User;
use App\Rules\PasswordPolicy;
use App\Support\UserFields;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Layout('components.layouts.web', ['active' => 'usuarios'])]
#[Title('Importar usuarios')]
class Importar extends Component
{
    use WithFileUploads;

    public const MAX_FILE_MB = 15;

    public const MAX_FILAS = 5000;

    public ?TemporaryUploadedFile $archivo = null;

    public bool $tieneEncabezados = true;

    public int $filaInicio = 1;

    public bool $procesado = false;

    /** @var array<int, array{indice:int, titulo:string}> */
    public array $columnas = [];

    /** @var array<int, array<int, string>> */
    public array $muestras = [];

    /** @var array<int, string> indiceColumna => campo destino ('' = no usar) */
    public array $mapeo = [];

    public int $totalFilas = 0;

    /** @var array<int, array{fila:int, columna:string, motivo:string}> */
    public array $errores = [];

    public function mount(): void
    {
        abort_unless(Gate::allows('usuarios.importar'), 403);
    }

    /**
     * Campos a los que se puede mapear una columna del archivo.
     *
     * @return array<string, string>
     */
    public function camposDisponibles(): array
    {
        $campos = [
            'username' => 'Usuario (login)  (obligatorio)',
            'password' => 'Contraseña  (obligatoria)',
            'nombre' => 'Nombre  (obligatorio)',
            'apellidos' => 'Apellidos',
            'email' => 'Email',
            'dni' => 'DNI',
            'cif' => 'CIF',
            'telefono' => 'Teléfono',
            'numero_empleado' => 'Nº empleado',
            'tipo_usuario' => 'Tipo (interno/externo)  (obligatorio)',
            'rol' => 'Rol  (obligatorio)',
            'cliente_codigo' => 'Empresa (código cliente)  (si externo)',
            'acceso' => 'Acceso (web/móvil/ambos)',
            'activo' => 'Activo (sí/no)',
        ];

        // Tasas: solo si el actor tiene permiso de gestionar tarifas.
        if (auth()->user()?->can('usuarios.gestionar_tarifas')) {
            $campos['tasa_hora'] = 'Tasa base (€/hora)';
            $campos['tasa_extra'] = 'Tasa extra (€/hora)';
            $campos['tasa_festivo'] = 'Tasa festivo (€/hora)';
        }

        return $campos;
    }

    /** @return array<string, array<int, string>> */
    private function aliasCampos(): array
    {
        return [
            'username' => ['usuario', 'username', 'login', 'user'],
            'password' => ['password', 'contrasena', 'clave', 'pass'],
            'nombre' => ['nombre'],
            'apellidos' => ['apellidos', 'apellido'],
            'email' => ['email', 'correo', 'e mail', 'mail', 'correo electronico'],
            'dni' => ['dni', 'nif'],
            'cif' => ['cif'],
            'telefono' => ['telefono', 'tlf', 'tel', 'movil', 'phone', 'contacto'],
            'numero_empleado' => ['n empleado', 'numero empleado', 'nº empleado', 'cod empleado', 'codigo empleado', 'id empleado', 'empleado'],
            'tipo_usuario' => ['tipo', 'tipo usuario', 'tipo de usuario'],
            'rol' => ['rol', 'role', 'cargo', 'perfil'],
            'cliente_codigo' => ['empresa', 'cliente', 'codigo cliente', 'cod cliente', 'codigo empresa', 'empresa codigo'],
            'acceso' => ['acceso', 'access'],
            'activo' => ['activo', 'estado', 'active', 'alta'],
            'tasa_hora' => ['tasa', 'tasa hora', 'tasa base', 'precio hora', 'tarifa', 'tarifa hora'],
            'tasa_extra' => ['tasa extra', 'precio extra', 'tarifa extra', 'extras'],
            'tasa_festivo' => ['tasa festivo', 'tasa fest', 'precio festivo', 'tarifa festivo', 'festivo'],
        ];
    }

    /**
     * Convierte una celda de Excel a float decimal o null.
     * Acepta "22.191", "22,191", "22.191 €/h", "  ". Devuelve null si vacío.
     */
    private function parsearDecimal(string $valor): ?float
    {
        $v = trim($valor);
        if ($v === '') {
            return null;
        }
        // Quita símbolos comunes (€, /h, etc.) y normaliza coma decimal a punto.
        $v = preg_replace('/[€$\s]|\/h/i', '', $v) ?? $v;
        $v = str_replace(',', '.', $v);

        return is_numeric($v) ? (float) $v : null;
    }

    private function normalizar(string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim($texto)));
        $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto) ?? '';

        return trim($texto);
    }

    /**
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

        $num = count($filasDatos);
        if ($num > self::MAX_FILAS) {
            $this->addError('archivo', 'El archivo tiene '.number_format($num, 0, ',', '.').' filas. El máximo por importación es '.number_format(self::MAX_FILAS, 0, ',', '.').'. Divide el archivo en varias partes.');

            return;
        }

        $this->totalFilas = $num;
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

        return in_array($v, ['1', 'si', 's', 'true', 'x', 'y', 'yes', 'activo', 'verdadero', 'alta'], true);
    }

    private function normalizarTipoUsuario(string $valor): ?string
    {
        $v = $this->normalizar($valor);

        return match ($v) {
            'interno', 'int' => 'interno',
            'externo', 'ext' => 'externo',
            default => null,
        };
    }

    private function normalizarAcceso(string $valor): ?string
    {
        $v = $this->normalizar($valor);

        return match ($v) {
            'web' => 'web',
            'movil', 'mobil', 'mobile' => 'movil',
            'ambos', 'both' => 'ambos',
            default => null,
        };
    }

    public function importar(): void
    {
        abort_unless(Gate::allows('usuarios.importar'), 403);
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

        // Campos obligatorios para crear un usuario.
        $faltantes = array_values(array_diff(
            ['username', 'password', 'nombre', 'tipo_usuario', 'rol'],
            $destinos
        ));
        if ($faltantes !== []) {
            $labelsFalt = array_map(fn ($c) => $this->camposDisponibles()[$c] ?? $c, $faltantes);
            $this->addError('mapeo', 'Faltan columnas obligatorias: '.implode(', ', $labelsFalt));

            return;
        }

        // Leemos el archivo entero y reconstruimos filas igual que en procesarArchivo().
        $filas = $this->leerHoja();
        $filas = array_slice($filas, max(0, $this->filaInicio - 1), null, true);

        $tieneCab = $this->tieneEncabezados;
        $filaBaseHoja = $this->filaInicio + ($tieneCab ? 1 : 0);

        $indices = array_keys($filas);
        if ($tieneCab && $indices !== []) {
            array_shift($indices);
        }

        if (count($indices) > self::MAX_FILAS) {
            $this->addError('archivo', 'El archivo supera el máximo de '.number_format(self::MAX_FILAS, 0, ',', '.').' filas por importación.');

            return;
        }

        // Conjuntos para detectar duplicados de campos únicos (UserFields::uniqueFields).
        $camposUnicos = array_values(array_intersect(UserFields::uniqueFields(), $destinos));
        /** @var array<string, array<int, string>> $valoresBD */
        $valoresBD = [];
        /** @var array<string, array<int, string>> $valoresVistos */
        $valoresVistos = [];
        foreach ($camposUnicos as $campo) {
            $valoresBD[$campo] = User::query()
                ->whereNotNull($campo)->where($campo, '!=', '')
                ->pluck($campo)
                ->map(fn ($v) => mb_strtolower(trim((string) $v)))
                ->all();
            $valoresVistos[$campo] = [];
        }

        // Catálogo de roles (case-insensitive) y mapa de niveles/acceso por rol.
        $roles = Role::query()->where('guard_name', 'web')->get(['name', 'nivel', 'acceso']);
        /** @var array<string, Role> $rolesPorNombre */
        $rolesPorNombre = [];
        foreach ($roles as $r) {
            $rolesPorNombre[mb_strtolower($r->name)] = $r;
        }

        // Catálogo de clientes por código (clave para resolver "Empresa").
        $clientesPorCodigo = Cliente::query()->whereNotNull('codigo_cliente')->pluck('id', 'codigo_cliente')->all();

        /** @var User $actor */
        $actor = auth()->user();

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

            // Recolectar valores crudos.
            $crudo = [];
            foreach ($destinos as $colIndice => $campo) {
                $crudo[$campo] = isset($fila[$colIndice]) ? trim((string) $fila[$colIndice]) : '';
            }

            // Resolver tipo_usuario (interno/externo).
            $tipoRaw = (string) ($crudo['tipo_usuario'] ?? '');
            $tipo = $this->normalizarTipoUsuario($tipoRaw);
            if ($tipo === null) {
                $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Tipo', 'motivo' => "Tipo «{$tipoRaw}» no válido. Usa «interno» o «externo»."];
                $numeroLinea++;

                continue;
            }

            // Resolver rol (case-insensitive contra catálogo).
            $rolRaw = (string) ($crudo['rol'] ?? '');
            $rolNombre = $rolRaw !== '' ? (string) ($rolesPorNombre[mb_strtolower($rolRaw)]->name ?? '') : '';
            if ($rolNombre === '') {
                $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Rol', 'motivo' => "El rol «{$rolRaw}» no existe en el catálogo."];
                $numeroLinea++;

                continue;
            }

            // Permiso para asignar ese rol.
            if (! Gate::forUser($actor)->allows('puedeAsignarRol', [User::class, $rolNombre])) {
                $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Rol', 'motivo' => "No tienes permiso para crear usuarios con rol «{$rolNombre}»."];
                $numeroLinea++;

                continue;
            }

            // Resolver cliente_id si externo.
            $clienteId = null;
            if ($tipo === 'externo') {
                $codClienteRaw = trim((string) ($crudo['cliente_codigo'] ?? ''));
                if ($codClienteRaw === '') {
                    $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Empresa', 'motivo' => 'Tipo externo: se requiere código de cliente.'];
                    $numeroLinea++;

                    continue;
                }
                $codClienteInt = (int) $codClienteRaw;
                if ((string) $codClienteInt !== $codClienteRaw || ! isset($clientesPorCodigo[$codClienteInt])) {
                    $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Empresa', 'motivo' => "Código de cliente «{$codClienteRaw}» no encontrado."];
                    $numeroLinea++;

                    continue;
                }
                $clienteId = (int) $clientesPorCodigo[$codClienteInt];
            }

            // Resolver acceso (opcional → hereda del rol).
            $accesoRaw = (string) ($crudo['acceso'] ?? '');
            $acceso = null;
            if ($accesoRaw !== '') {
                $acceso = $this->normalizarAcceso($accesoRaw);
                if ($acceso === null) {
                    $erroresLocal[] = ['fila' => $numeroLinea, 'columna' => 'Acceso', 'motivo' => "Acceso «{$accesoRaw}» no válido. Usa «web», «móvil» o «ambos»."];
                    $numeroLinea++;

                    continue;
                }
            }
            // Si no se mapea o vacío → heredar acceso del rol. (No se persiste en
            // users porque ese campo es del rol, no del user; lo notamos por si
            // se quisiera usar para algo en el futuro.)
            $_ = $acceso ?? $rolesPorNombre[mb_strtolower($rolNombre)]->acceso ?? 'ambos';

            // activo (default true).
            $activo = in_array('activo', $destinos, true)
                ? $this->interpretarActivo((string) ($crudo['activo'] ?? ''))
                : true;

            // Datos finales para validar y persistir.
            $datos = [
                'username' => (string) ($crudo['username'] ?? ''),
                'password' => (string) ($crudo['password'] ?? ''),
                'nombre' => (string) ($crudo['nombre'] ?? ''),
                'apellidos' => $crudo['apellidos'] ?? null,
                'email' => $crudo['email'] ?? null,
                'dni' => $crudo['dni'] ?? null,
                'cif' => $crudo['cif'] ?? null,
                'telefono' => $crudo['telefono'] ?? null,
                'numero_empleado' => $crudo['numero_empleado'] ?? null,
                'tasa_hora' => in_array('tasa_hora', $destinos, true) ? $this->parsearDecimal((string) ($crudo['tasa_hora'] ?? '')) : null,
                'tasa_extra' => in_array('tasa_extra', $destinos, true) ? $this->parsearDecimal((string) ($crudo['tasa_extra'] ?? '')) : null,
                'tasa_festivo' => in_array('tasa_festivo', $destinos, true) ? $this->parsearDecimal((string) ($crudo['tasa_festivo'] ?? '')) : null,
                'tipo_usuario' => $tipo,
                'cliente_id' => $clienteId,
                'activo' => $activo,
                'rol' => $rolNombre,
            ];

            // Normalizar vacíos a null en opcionales.
            foreach (['apellidos', 'email', 'dni', 'cif', 'telefono', 'numero_empleado'] as $opt) {
                if (isset($datos[$opt]) && trim((string) $datos[$opt]) === '') {
                    $datos[$opt] = null;
                }
            }

            // Validación de formato (fuente: UserFields).
            $reglas = UserFields::getValidationRules();
            unset($reglas['cliente_id'], $reglas['tipo_usuario'], $reglas['rol']); // ya resueltos arriba
            // Misma política de fortaleza que en el formulario manual: el DNI
            // de un trabajador como contraseña inicial pasa, pero rechaza basura.
            $reglas['password'] = [
                'required', 'string', 'max:100',
                new PasswordPolicy([$datos['username'], $datos['nombre'], $datos['apellidos']]),
            ];

            $mensajes = [
                'required' => 'El campo :attribute es obligatorio.',
                'min' => 'El campo :attribute debe tener al menos :min caracteres.',
                'max' => 'El campo :attribute no puede superar :max.',
                'email' => 'El :attribute no tiene un formato válido.',
                'regex' => 'El :attribute solo admite letras, números, puntos, guiones y guiones bajos.',
                'boolean' => 'El campo :attribute no es válido.',
            ];

            $atributos = [
                'username' => 'Usuario',
                'password' => 'Contraseña',
                'nombre' => 'Nombre',
                'apellidos' => 'Apellidos',
                'email' => 'Email',
                'dni' => 'DNI',
                'cif' => 'CIF',
                'telefono' => 'Teléfono',
                'numero_empleado' => 'Nº empleado',
                'tasa_hora' => 'Tasa base',
                'tasa_extra' => 'Tasa extra',
                'tasa_festivo' => 'Tasa festivo',
            ];

            $validador = Validator::make($datos, $reglas, $mensajes, $atributos);

            if ($validador->fails()) {
                foreach ($validador->errors()->messages() as $campo => $msgs) {
                    $erroresLocal[] = [
                        'fila' => $numeroLinea,
                        'columna' => $this->camposDisponibles()[$campo] ?? $campo,
                        'motivo' => $msgs[0],
                    ];
                }
                $numeroLinea++;

                continue;
            }

            // Unicidad: username (vs BD + dentro del archivo).
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

        if ($erroresLocal !== []) {
            usort($erroresLocal, fn ($a, $b): int => $a['fila'] <=> $b['fila']);
            $this->errores = $erroresLocal;

            return;
        }

        DB::transaction(function () use ($aCrear): void {
            foreach ($aCrear as $datos) {
                $user = new User;
                $user->fill([
                    'username' => $datos['username'],
                    'password' => Hash::make($datos['password']),
                    'nombre' => $datos['nombre'],
                    'apellidos' => $datos['apellidos'],
                    'email' => $datos['email'],
                    'dni' => $datos['dni'],
                    'cif' => $datos['cif'],
                    'telefono' => $datos['telefono'],
                    'numero_empleado' => $datos['numero_empleado'],
                    'tasa_hora' => $datos['tasa_hora'] ?? null,
                    'tasa_extra' => $datos['tasa_extra'] ?? null,
                    'tasa_festivo' => $datos['tasa_festivo'] ?? null,
                    'tipo_usuario' => $datos['tipo_usuario'],
                    'cliente_id' => $datos['cliente_id'],
                    'activo' => $datos['activo'],
                ]);
                $user->save();
                $user->syncRoles([$datos['rol']]);
            }
        });

        $n = count($aCrear);
        session()->flash('status', $n.' '.($n === 1 ? 'usuario importado' : 'usuarios importados').' correctamente.');
        $this->redirectRoute('usuarios.index', navigate: true);
    }

    public function reiniciar(): void
    {
        $this->reset(['archivo', 'procesado', 'columnas', 'muestras', 'mapeo', 'totalFilas', 'errores']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.usuarios.importar', [
            'maxMb' => self::MAX_FILE_MB,
            'maxFilas' => self::MAX_FILAS,
        ]);
    }
}
