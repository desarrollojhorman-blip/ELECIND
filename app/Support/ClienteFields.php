<?php

namespace App\Support;

/**
 * Centraliza validación y metadatos de los campos de Cliente.
 *
 * Espejo de {@see AjustesFields}: fuente única de verdad para reglas de
 * validación + límites de UI (maxlength) + textos de ayuda, de forma que el
 * backend y el formulario no se desincronicen.
 *
 * Nota: la CONSTRUCCIÓN de la regla `unique` no vive aquí porque depende del
 * registro en edición (ignorarse a sí mismo) y del soft-delete; se arma en
 * ClienteForm::rules(). Pero QUÉ campos son únicos sí vive aquí
 * ({@see self::uniqueFields()}), fuente única compartida con la importación,
 * para que nunca se desincronicen.
 *
 * Disciplina: los `max` de validación son ≤ tamaño real de la columna en BD.
 * `codigo_cliente max:100000` es un tope de NEGOCIO (la columna int aguanta
 * más); lo aplica solo la app, no la base de datos.
 */
class ClienteFields
{
    /**
     * Plantillas de validación reutilizables.
     *
     * @return array<string, array<int, string>>
     */
    public static function validationTemplates(): array
    {
        return [
            'codigo' => ['required', 'integer', 'min:1', 'max:100000'],
            'nombre' => ['required', 'string', 'max:150'],
            'texto_150' => ['nullable', 'string', 'max:150'],
            'texto_255' => ['nullable', 'string', 'max:255'],
            'texto_120' => ['nullable', 'string', 'max:120'],
            'cif' => ['nullable', 'string', 'max:20'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'booleano' => ['boolean'],
        ];
    }

    /**
     * Configuración por campo: tipo de input, plantilla de validación y ayuda.
     *
     * @return array<string, array{type: string, validation: string, help?: string}>
     */
    public static function config(): array
    {
        return [
            'codigo_cliente' => ['type' => 'number', 'validation' => 'codigo',        'help' => 'Número entre 1 y 100000.'],
            'nombre' => ['type' => 'text',   'validation' => 'nombre'],
            'nombre_comercial' => ['type' => 'text',   'validation' => 'texto_150'],
            'cif' => ['type' => 'text',   'validation' => 'cif'],
            'direccion' => ['type' => 'text',   'validation' => 'texto_255'],
            'codigo_postal' => ['type' => 'text',   'validation' => 'codigo_postal'],
            'poblacion' => ['type' => 'text',   'validation' => 'texto_120'],
            'provincia' => ['type' => 'text',   'validation' => 'texto_120'],
            'telefono' => ['type' => 'text',   'validation' => 'telefono'],
            'email' => ['type' => 'email',  'validation' => 'email'],
            'observaciones' => ['type' => 'textarea', 'validation' => 'observaciones'],
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
     * Fuente única de verdad: lo leen tanto ClienteForm::rules() (para
     * construir el `Rule::unique`) como la importación (Clientes\Importar)
     * para su comprobación en lote.
     * Decisión de negocio: el CIF puede repetirse → NO está en la lista.
     *
     * @return array<int, string>
     */
    public static function uniqueFields(): array
    {
        return ['codigo_cliente'];
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
     * Devuelve null si no aplica (p. ej. codigo_cliente es numérico).
     */
    public static function getMaxLength(string $name): ?int
    {
        $field = self::getField($name);
        if ($field === null || $field['type'] === 'number') {
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
