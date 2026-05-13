<?php

namespace App\Livewire\Forms;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RoleForm extends Form
{
    public ?int $id = null;

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
            'name' => 'nombre del rol',
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
            'name.regex' => 'El nombre debe estar en minúsculas, sin espacios. Solo letras, números y guiones bajos.',
        ];
    }

    public function fromModel(Role $rol): void
    {
        $this->id = (int) $rol->getKey();
        $this->name = $rol->name;
        $this->acceso = $rol->acceso;
        $this->nivel = $rol->nivel;
        $this->es_sistema = (bool) $rol->es_sistema;
        $this->permisos = $rol->permissions->pluck('id')->map(fn ($i) => (int) $i)->all();
    }

    public function save(User $creador): Role
    {
        $this->validate();

        $esNuevo = $this->id === null;

        if ($esNuevo) {
            $rol = new Role([
                'name' => $this->name,
                'guard_name' => 'web',
                'acceso' => $this->acceso,
                'nivel' => $this->nivel,
                'es_sistema' => false,
            ]);
            $rol->save();
        } else {
            /** @var Role $rol */
            $rol = Role::findOrFail($this->id);

            // Roles del sistema: protegemos name + es_sistema; solo permisos y opcionalmente nivel/acceso (vía superadmin).
            if (! $rol->es_sistema) {
                $rol->name = $this->name;
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
