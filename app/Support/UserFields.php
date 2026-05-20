<?php

namespace App\Support;

/**
 * Centraliza validación y metadatos de los campos de User.
 *
 * Espejo de ClienteFields: fuente única de verdad para reglas de validación +
 * límites de UI (maxlength), de forma que el formulario, la importación y la
 * exportación no se desincronicen.
 *
 * Nota: la CONSTRUCCIÓN de la regla `unique` no vive aquí porque depende del
 * registro en edición (ignorarse a sí mismo); se arma en UserForm::rules().
 * Pero QUÉ campos son únicos sí vive aquí ({@see self::uniqueFields()}),
 * fuente única compartida con la importación.
 *
 * Disciplina: los `max` de validación son ≤ tamaño real de la columna en BD.
 */
class UserFields
{
    /**
     * Plantillas de validación reutilizables.
     *
     * @return array<string, array<int, string>>
     */
    public static function validationTemplates(): array
    {
        return [
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/'],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'documento_20' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'numero_empleado' => ['nullable', 'string', 'max:30'],
            'tipo_usuario' => ['required', 'in:interno,externo'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'rol' => ['required', 'exists:roles,name'],
            'booleano' => ['boolean'],
        ];
    }

    /**
     * Configuración por campo.
     *
     * @return array<string, array{type: string, validation: string, help?: string}>
     */
    public static function config(): array
    {
        return [
            'username' => ['type' => 'text', 'validation' => 'username', 'help' => 'Minúsculas, números, puntos, guiones y guiones bajos.'],
            'password' => ['type' => 'password', 'validation' => 'password'],
            'nombre' => ['type' => 'text', 'validation' => 'nombre'],
            'apellidos' => ['type' => 'text', 'validation' => 'apellidos'],
            'email' => ['type' => 'email', 'validation' => 'email'],
            'dni' => ['type' => 'text', 'validation' => 'documento_20'],
            'cif' => ['type' => 'text', 'validation' => 'documento_20'],
            'telefono' => ['type' => 'text', 'validation' => 'telefono'],
            'numero_empleado' => ['type' => 'text', 'validation' => 'numero_empleado'],
            'tipo_usuario' => ['type' => 'select', 'validation' => 'tipo_usuario'],
            'cliente_id' => ['type' => 'number', 'validation' => 'cliente_id'],
            'rol' => ['type' => 'select', 'validation' => 'rol'],
            'activo' => ['type' => 'checkbox', 'validation' => 'booleano'],
        ];
    }

    /**
     * Reglas de validación base (sin los `unique` dinámicos).
     *
     * @return array<string, array<int, string>>
     */
    public static function getValidationRules(): array
    {
        $rules = [];
        $templates = self::validationTemplates();

        foreach (self::config() as $field => $config) {
            $rules[$field] = $templates[$config['validation']];
        }

        return $rules;
    }

    /**
     * Campos con restricción de unicidad (registros NO en papelera).
     *
     * Fuente única compartida con la importación y con UserForm::rules().
     * Decisión de negocio: SOLO `username` es único (es la clave del login).
     * Email, DNI, CIF, número empleado, teléfono → pueden repetirse.
     *
     * @return array<int, string>
     */
    public static function uniqueFields(): array
    {
        return ['username'];
    }

    /**
     * @return array{type: string, validation: string, help?: string}|null
     */
    public static function getField(string $name): ?array
    {
        return self::config()[$name] ?? null;
    }

    /**
     * Máximo de caracteres de un campo de texto (para maxlength en el input).
     */
    public static function getMaxLength(string $name): ?int
    {
        $field = self::getField($name);
        if ($field === null || ! in_array($field['type'], ['text', 'email', 'password'], true)) {
            return null;
        }

        $template = self::validationTemplates()[$field['validation']] ?? [];

        foreach ($template as $rule) {
            if (str_starts_with($rule, 'max:')) {
                return (int) substr($rule, 4);
            }
        }

        return null;
    }
}
