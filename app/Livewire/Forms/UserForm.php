<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Rules\PasswordPolicy;
use App\Support\UserFields;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public string $username = '';

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $apellidos = null;

    #[Validate]
    public ?string $email = null;

    #[Validate]
    public ?string $dni = null;

    #[Validate]
    public ?string $cif = null;

    #[Validate]
    public ?string $telefono = null;

    #[Validate]
    public ?string $numero_empleado = null;

    /**
     * Tarifas (€/hora) — 8 tasas por trabajador.
     *
     * Vinculadas a users.tasa_*. Se pueden editar desde dos sitios distintos
     * y ambos persisten en las mismas columnas:
     *   - Ficha del usuario (pestaña "Tarifas"), si el rol es interno.
     *   - Módulo dedicado /tarifas/trabajadores (vista masiva).
     *
     * NOT NULL DEFAULT 0 en BD. Si el rol final es externo, save() las
     * fuerza a 0 ignorando lo que haya en el form.
     */
    #[Validate]
    public ?string $tasa_hora = null;

    #[Validate]
    public ?string $tasa_lab_noche = null;

    #[Validate]
    public ?string $tasa_festivo = null;

    #[Validate]
    public ?string $tasa_fest_noche = null;

    #[Validate]
    public ?string $tasa_extra = null;

    #[Validate]
    public ?string $tasa_ex_lab_noc = null;

    #[Validate]
    public ?string $tasa_ex_fes = null;

    #[Validate]
    public ?string $tasa_ex_fes_noct = null;

    /**
     * interno | externo — DERIVADO del rol (no se edita en el form).
     * Se rellena automáticamente en fromModel() y save() a partir de
     * roles.es_externo. Se mantiene como propiedad por retrocompatibilidad
     * (consultas existentes que filtran por tipo_usuario).
     */
    public string $tipo_usuario = 'interno';

    #[Validate]
    public ?int $cliente_id = null;

    /** Nombre del rol Spatie: superadmin | administrador | trabajador | responsable | custom */
    #[Validate]
    public string $rol = 'trabajador';

    /**
     * IDs de los clientes que gestiona (solo roles con solo_clientes_asignados).
     * Se castean a int al guardar.
     *
     * @var array<int|string>
     */
    public array $clientesGestionados = [];

    /** Marca "ve todos los clientes (presentes y futuros)". Solo aplica a roles scoped. */
    public bool $gestionaTodosClientes = false;

    #[Validate]
    public bool $activo = true;

    #[Validate]
    public ?string $password = null;

    /**
     * Reglas base centralizadas en {@see UserFields} + reglas dinámicas
     * (unique, password required al crear, cliente_id required si externo).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $esNuevo = $this->id === null;

        // Reglas base — fuente única.
        $rules = UserFields::getValidationRules();

        // Quitamos la regla 'password' base si no se va a crear/cambiar:
        // se vuelve a añadir abajo con el matiz "required al crear".
        unset($rules['password']);

        // Únicos dinámicos: QUÉ campos son únicos vive en UserFields.
        // Aquí solo se arma la regla (depende del id en edición).
        foreach (UserFields::uniqueFields() as $campo) {
            $rules[$campo][] = Rule::unique('users', $campo)->ignore($this->id);
        }

        // Las reglas dinámicas de cliente dependen ahora del ROL (es_externo /
        // solo_clientes_asignados), no del tipo_usuario manual.
        $rolObj = \App\Models\Role::firstWhere('name', $this->rol);
        $rolEsExterno = (bool) $rolObj?->es_externo;
        $rolEsScoped  = (bool) $rolObj?->solo_clientes_asignados;

        // cliente_id: required SOLO si el rol es externo (responsable y similares).
        $rules['cliente_id'] = array_merge(
            [Rule::requiredIf(fn (): bool => $rolEsExterno)],
            $rules['cliente_id'] ?? []
        );

        // clientesGestionados: required (≥1) solo si el rol es scoped Y NO ve "todos".
        if ($rolEsScoped && ! $this->gestionaTodosClientes) {
            $rules['clientesGestionados']   = ['required', 'array', 'min:1'];
            $rules['clientesGestionados.*'] = ['integer', 'exists:clientes,id'];
        } else {
            $rules['clientesGestionados']   = ['array'];
            $rules['clientesGestionados.*'] = ['integer', 'exists:clientes,id'];
        }

        // tipo_usuario: ya no es editable. Se deriva del rol al guardar.
        unset($rules['tipo_usuario']);

        // password: required al crear, nullable al editar. Política de fortaleza:
        // ≥8 caracteres, una letra y un número, no contiene username/nombre/apellidos,
        // no es una contraseña común. Un DNI/NIF español la cumple por defecto.
        $rules['password'] = [
            $esNuevo ? 'required' : 'nullable',
            'string', 'max:100',
            new PasswordPolicy([$this->username, $this->nombre, $this->apellidos]),
        ];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'username' => 'usuario',
            'nombre' => 'nombre',
            'apellidos' => 'apellidos',
            'email' => 'email',
            'dni' => 'DNI',
            'cif' => 'CIF',
            'telefono' => 'teléfono',
            'numero_empleado' => 'nº empleado',
            'tasa_hora' => 'tasa labor',
            'tasa_lab_noche' => 'tasa labor noche',
            'tasa_festivo' => 'tasa festivo',
            'tasa_fest_noche' => 'tasa festivo noche',
            'tasa_extra' => 'tasa extra laboral',
            'tasa_ex_lab_noc' => 'tasa extra laboral noche',
            'tasa_ex_fes' => 'tasa extra festivo',
            'tasa_ex_fes_noct' => 'tasa extra festivo noche',
            'tipo_usuario' => 'tipo de usuario',
            'cliente_id' => 'cliente',
            'rol' => 'rol',
            'activo' => 'activo',
            'password' => 'contraseña',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'El usuario es obligatorio.',
            'username.unique' => 'Ese usuario ya está en uso.',
            'username.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'nombre.required' => 'El nombre es obligatorio.',
            'email.email' => 'El email no tiene un formato válido.',
            'cliente_id.required' => 'Debes seleccionar un cliente para este rol.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'clientesGestionados.required' => 'Selecciona al menos un cliente o marca "Asignar todos los clientes".',
            'clientesGestionados.min' => 'Selecciona al menos un cliente o marca "Asignar todos los clientes".',
            'rol.required' => 'El rol es obligatorio.',
            'rol.exists' => 'El rol seleccionado no existe.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.max' => 'La contraseña no puede superar :max caracteres.',
        ];
    }

    /** Vacío/null → 0. Soporta coma como separador decimal. */
    private function parseTasa(?string $valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', $valor);
    }

    public function fromModel(User $user): void
    {
        $this->id = (int) $user->getKey();
        $this->username = $user->username;
        $this->nombre = $user->nombre;
        $this->apellidos = $user->apellidos;
        $this->email = $user->email;
        $this->dni = $user->dni;
        $this->cif = $user->cif;
        $this->telefono = $user->telefono;
        $this->numero_empleado = $user->numero_empleado;
        $this->tasa_hora = (string) $user->tasa_hora;
        $this->tasa_lab_noche = (string) $user->tasa_lab_noche;
        $this->tasa_festivo = (string) $user->tasa_festivo;
        $this->tasa_fest_noche = (string) $user->tasa_fest_noche;
        $this->tasa_extra = (string) $user->tasa_extra;
        $this->tasa_ex_lab_noc = (string) $user->tasa_ex_lab_noc;
        $this->tasa_ex_fes = (string) $user->tasa_ex_fes;
        $this->tasa_ex_fes_noct = (string) $user->tasa_ex_fes_noct;
        $this->tipo_usuario = $user->tipo_usuario;
        $this->cliente_id = $user->cliente_id;
        $this->activo = (bool) $user->activo;
        $this->rol = $user->getRoleNames()->first() ?? 'trabajador';
        $this->password = null;
        $this->clientesGestionados = $user->clientesGestionados()->pluck('id')
            ->map(fn ($id) => (int) $id)->all();
        $this->gestionaTodosClientes = (bool) $user->gestiona_todos_clientes;
    }

    public function save(): User
    {
        $this->validate();

        // Derivar todo lo dependiente del rol antes de guardar.
        $rolObj       = \App\Models\Role::firstWhere('name', $this->rol);
        $rolEsExterno = (bool) $rolObj?->es_externo;
        $rolEsScoped  = (bool) $rolObj?->solo_clientes_asignados;

        $tipoUsuario           = $rolEsExterno ? 'externo' : 'interno';
        $this->tipo_usuario    = $tipoUsuario; // sincronizar la propiedad pública por si el blade la lee
        $clienteIdFinal        = $rolEsExterno ? $this->cliente_id : null;
        $gestionaTodosFinal    = $rolEsScoped ? $this->gestionaTodosClientes : false;

        $datos = [
            'username' => $this->username,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'dni' => $this->dni,
            'cif' => $this->cif,
            'telefono' => $this->telefono,
            'numero_empleado' => $this->numero_empleado,
            'tipo_usuario' => $tipoUsuario,
            'cliente_id' => $clienteIdFinal,
            'gestiona_todos_clientes' => $gestionaTodosFinal,
            'activo' => $this->activo,
        ];

        // Tarifas (8 columnas tasa_*):
        //   - Rol INTERNO: se guardan los valores del form (pestaña "Tarifas").
        //     Vacío → 0. La pantalla /tarifas/trabajadores edita lo mismo, por
        //     lo que ambos sitios actualizan la misma BD.
        //   - Rol EXTERNO: se FUERZAN a 0. Ignora lo que haya en el form
        //     (impide rellenar tasas y cambiar a externo antes de guardar) Y
        //     resetea valores previos si pasa de interno a externo.
        if ($rolEsExterno) {
            $datos += [
                'tasa_hora' => 0.0,
                'tasa_lab_noche' => 0.0,
                'tasa_festivo' => 0.0,
                'tasa_fest_noche' => 0.0,
                'tasa_extra' => 0.0,
                'tasa_ex_lab_noc' => 0.0,
                'tasa_ex_fes' => 0.0,
                'tasa_ex_fes_noct' => 0.0,
            ];
        } else {
            $datos += [
                'tasa_hora' => $this->parseTasa($this->tasa_hora),
                'tasa_lab_noche' => $this->parseTasa($this->tasa_lab_noche),
                'tasa_festivo' => $this->parseTasa($this->tasa_festivo),
                'tasa_fest_noche' => $this->parseTasa($this->tasa_fest_noche),
                'tasa_extra' => $this->parseTasa($this->tasa_extra),
                'tasa_ex_lab_noc' => $this->parseTasa($this->tasa_ex_lab_noc),
                'tasa_ex_fes' => $this->parseTasa($this->tasa_ex_fes),
                'tasa_ex_fes_noct' => $this->parseTasa($this->tasa_ex_fes_noct),
            ];
        }

        if ($this->password !== null && $this->password !== '') {
            $datos['password'] = Hash::make($this->password);
        }

        if ($this->id === null) {
            $user = new User;
        } else {
            /** @var User $user */
            $user = User::findOrFail($this->id);
        }

        $user->fill($datos);
        $user->save();

        $user->syncRoles([$this->rol]);

        // Pivote de clientes gestionados:
        //  - Rol scoped + "todos los clientes": pivote vacía (el flag ya hace el trabajo).
        //  - Rol scoped + lista concreta: sincroniza esa lista.
        //  - Rol no scoped: pivote vacía (no debería tener nada que gestionar).
        if ($rolEsScoped && ! $gestionaTodosFinal) {
            $user->clientesGestionados()->sync(array_map('intval', $this->clientesGestionados));
        } else {
            $user->clientesGestionados()->detach();
        }

        return $user;
    }

    /**
     * Genera un username único partiendo del nombre + apellidos.
     * "Juan" → "juan". Si existe → "juan.2", "juan.3", etc.
     * Incluye soft-deleted en la búsqueda para evitar colisiones al restaurar.
     */
    public static function sugerirUsername(string $nombre, ?string $apellidos = null): string
    {
        $base = Str::slug(trim($nombre), '.');

        if ($base === '' && $apellidos !== null) {
            $base = Str::slug(trim($apellidos), '.');
        }

        if ($base === '') {
            $base = 'usuario';
        }

        $candidato = $base;
        $i = 2;

        while (User::withTrashed()->where('username', $candidato)->exists()) {
            $candidato = $base.'.'.$i;
            $i++;
        }

        return $candidato;
    }

    /**
     * Busca usuarios distintos al actual que compartan email, DNI o CIF.
     * NO bloquea el guardado: el componente decide qué hacer.
     *
     * @return array<int, array{campo: string, valor: string, usuario_id: int, usuario_nombre: string, eliminado: bool}>
     */
    public function buscarDuplicados(): array
    {
        $coincidencias = [];

        foreach (['email', 'dni', 'cif'] as $campo) {
            $valor = $this->{$campo};

            if ($valor === null || trim((string) $valor) === '') {
                continue;
            }

            $duplicado = User::withTrashed()
                ->where($campo, $valor)
                ->when($this->id !== null, fn ($q) => $q->where('id', '!=', $this->id))
                ->first();

            if ($duplicado === null) {
                continue;
            }

            $coincidencias[] = [
                'campo' => $campo,
                'valor' => (string) $valor,
                'usuario_id' => (int) $duplicado->getKey(),
                'usuario_nombre' => trim($duplicado->nombre.' '.$duplicado->apellidos),
                'eliminado' => $duplicado->trashed(),
            ];
        }

        return $coincidencias;
    }
}
