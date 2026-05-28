<?php

namespace App\Livewire\Forms;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RoleForm extends Form
{
    public ?int $id = null;

    /** Nombre visible (libre: letras, números y espacios). */
    #[Validate]
    public string $etiqueta = '';

    /** Identificador interno tipo slug. Se genera desde la etiqueta al crear. */
    #[Validate]
    public string $name = '';

    /** web | movil | ambos */
    #[Validate]
    public string $acceso = 'web';

    #[Validate]
    public int $nivel = 10;

    public bool $es_sistema = false;

    /** @var array<int, int> ids de permisos seleccionados */
    #[Validate]
    public array $permisos = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'etiqueta' => ['required', 'string', 'max:80', 'regex:/^[\pL\pN ]+$/u'],
            'name' => [
                'required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')->ignore($this->id),
            ],
            'acceso' => ['required', Rule::in(['web', 'movil', 'ambos'])],
            'nivel' => ['required', 'integer', 'min:1', 'max:100'],
            'permisos' => ['array'],
            'permisos.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'etiqueta' => 'nombre',
            'name' => 'nombre interno',
            'acceso' => 'ámbito',
            'nivel' => 'nivel',
            'permisos' => 'permisos',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'etiqueta.regex' => 'El nombre solo admite letras, números y espacios (sin caracteres especiales).',
            'name.regex' => 'El nombre interno debe estar en minúsculas, sin espacios. Solo letras, números y guiones bajos.',
            'name.unique' => 'Ya existe un rol con ese nombre interno. Cambia ligeramente el nombre.',
        ];
    }

    public function fromModel(Role $rol): void
    {
        $this->id = (int) $rol->getKey();
        $this->etiqueta = $rol->etiqueta ?: $rol->nombreVisible();
        $this->name = $rol->name;
        $this->acceso = $rol->acceso;
        $this->nivel = $rol->nivel;
        $this->es_sistema = (bool) $rol->es_sistema;
        $this->permisos = $rol->permissions->pluck('id')->map(fn ($i) => (int) $i)->all();
    }

    public function save(User $creador): Role
    {
        $esNuevo = $this->id === null;

        // El nombre interno se genera desde la etiqueta SOLO al crear; después es
        // inmutable (se referencia en código/middlewares). Se calcula antes de
        // validar para que la regla unique evalúe el slug definitivo.
        if ($esNuevo) {
            $this->name = Str::slug($this->etiqueta, '_');
        }

        $this->validate();

        if ($esNuevo) {
            $rol = new Role([
                'name' => $this->name,
                'etiqueta' => $this->etiqueta,
                'guard_name' => 'web',
                'acceso' => $this->acceso,
                'nivel' => $this->nivel,
                'es_sistema' => false,
            ]);
            $rol->save();
        } else {
            /** @var Role $rol */
            $rol = Role::findOrFail($this->id);

            // Roles del sistema: name + etiqueta protegidos. El name interno
            // nunca se cambia tras crear; solo la etiqueta visible (no-sistema).
            if (! $rol->es_sistema) {
                $rol->etiqueta = $this->etiqueta;
            }
            $rol->acceso = $this->acceso;
            $rol->nivel = $this->nivel;
            $rol->save();
        }

        // Filtrar permisos a los compatibles con el ámbito + que el creador tenga.
        $idsValidos = $this->permisosAsignablesPor($creador, $rol->acceso)
            ->whereIn('id', $this->permisos)
            ->pluck('id')
            ->all();

        $rol->syncPermissions(Permission::whereIn('id', $idsValidos)->get());

        $this->id = (int) $rol->getKey();

        return $rol;
    }

    /**
     * Catálogo de permisos que un usuario concreto puede asignar a un rol con
     * el ámbito indicado. Aplica las dos reglas:
     *   1. Compatibilidad de ámbito.
     *   2. Delegación: solo permisos que el creador tenga.
     *
     * El superadmin ve TODOS los permisos compatibles con el ámbito (no filtra
     * por delegación porque ya tiene todos por definición).
     *
     * @return Collection<int, Permission>
     */
    public function permisosAsignablesPor(User $creador, string $ambito): Collection
    {
        $compatibles = match ($ambito) {
            'web' => ['web', 'ambos'],
            'movil' => ['movil', 'ambos'],
            'ambos' => ['web', 'movil', 'ambos'],
            default => [],
        };

        $query = Permission::query()->whereIn('ambito', $compatibles)->orderBy('categoria')->orderBy('name');

        if (! $creador->hasRole('superadmin')) {
            $permisosDelCreador = $creador->getAllPermissions()->pluck('name')->all();
            $query->whereIn('name', $permisosDelCreador);
        }

        return $query->get();
    }
}
