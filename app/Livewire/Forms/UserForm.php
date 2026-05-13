<?php

namespace App\Livewire\Forms;

use App\Models\User;
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

    /** interno | externo */
    #[Validate]
    public string $tipo_usuario = 'interno';

    #[Validate]
    public ?int $cliente_id = null;

    /** Nombre del rol Spatie: superadmin | administrador | trabajador | responsable */
    #[Validate]
    public string $rol = 'trabajador';

    #[Validate]
    public bool $activo = true;

    #[Validate]
    public ?string $password = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $esNuevo = $this->id === null;

        return [
            'username' => [
                'required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($this->id),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'dni' => ['nullable', 'string', 'max:20'],
            'cif' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'tipo_usuario' => ['required', Rule::in(['interno', 'externo'])],
            'cliente_id' => [
                Rule::requiredIf(fn (): bool => $this->tipo_usuario === 'externo'),
                'nullable', 'integer', 'exists:clientes,id',
            ],
            'rol' => ['required', Rule::exists('roles', 'name')],
            'activo' => ['boolean'],
            'password' => [
                $esNuevo ? 'required' : 'nullable',
                'nullable', 'string', 'min:6',
            ],
        ];
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
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, puntos, guiones y guiones bajos.',
            'nombre.required' => 'El nombre es obligatorio.',
            'email.email' => 'El email no tiene un formato válido.',
            'tipo_usuario.required' => 'El tipo de usuario es obligatorio.',
            'tipo_usuario.in' => 'El tipo de usuario seleccionado no es válido.',
            'cliente_id.required' => 'Debes seleccionar un cliente cuando el tipo de usuario es externo.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
        ];
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
        $this->tipo_usuario = $user->tipo_usuario;
        $this->cliente_id = $user->cliente_id;
        $this->activo = (bool) $user->activo;
        $this->rol = $user->getRoleNames()->first() ?? 'trabajador';
        $this->password = null;
    }

    public function save(): User
    {
        $this->validate();

        $datos = [
            'username' => $this->username,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'dni' => $this->dni,
            'cif' => $this->cif,
            'telefono' => $this->telefono,
            'tipo_usuario' => $this->tipo_usuario,
            'cliente_id' => $this->tipo_usuario === 'externo' ? $this->cliente_id : null,
            'activo' => $this->activo,
        ];

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
