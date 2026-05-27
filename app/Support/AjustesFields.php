<?php

namespace App\Support;

/**
 * Centraliza la configuración de validación y metadatos para el módulo Ajustes.
 *
 * Todos los campos de configuración (límites, patrones, textos de ayuda) se
 * definen en un único lugar, garantizando coherencia y facilidad de mantenimiento.
 *
 * @example
 * // Obtener reglas de validación
 * $rules = AjustesFields::getValidationRules();
 *
 * // Obtener config de un campo específico
 * $config = AjustesFields::getField('color_primario');
 */
class AjustesFields
{
    /**
     * Plantillas de validación reutilizables.
     *
     * Agrupa reglas que se repiten en múltiples campos.
     */
    public static function validationTemplates(): array
    {
        return [
            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-F]{6}$/i',
                'max:7',
            ],
            'template_60' => [
                'required',
                'string',
                'max:60',
            ],
            'token_dias' => [
                'required',
                'integer',
                'min:1',
                'max:90',
            ],
            'archivo_size' => [
                'required',
                'integer',
                'in:2,5,10,20,50',
            ],
            'archivo_count' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'prefijo_10' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Za-z0-9]+$/',
            ],
        ];
    }

    /**
     * Configuración de todos los campos del módulo Ajustes.
     *
     * Cada campo especifica:
     * - type: tipo de input (text, color, number, select)
     * - validation: nombre de la plantilla en validationTemplates()
     * - help: texto de ayuda para el usuario
     * - default: valor por defecto (opcional)
     * - options: valores disponibles para select (opcional)
     */
    public static function config(): array
    {
        return [
            // ──────────────────────────────────────────────────────────────
            // COLORES (usan template 'color': hex format, max 7 chars)
            // ──────────────────────────────────────────────────────────────
            'color_primario' => [
                'type' => 'color',
                'validation' => 'color',
                'help' => 'Formato: #RRGGBB (ej: #334155)',
                'default' => '#334155',
            ],
            'color_secundario' => [
                'type' => 'color',
                'validation' => 'color',
                'help' => 'Formato: #RRGGBB (ej: #f1f5f9)',
                'default' => '#f1f5f9',
            ],
            'color_texto_encabezado' => [
                'type' => 'color',
                'validation' => 'color',
                'help' => 'Formato: #RRGGBB (ej: #ffffff)',
                'default' => '#ffffff',
            ],

            // ──────────────────────────────────────────────────────────────
            // PLANTILLAS DE NUMERACIÓN (todas usan template_60: max 60 chars)
            // ──────────────────────────────────────────────────────────────
            'plantilla_numeracion_albaran' => [
                'type' => 'text',
                'validation' => 'template_60',
                'help' => 'Ej: ALB-{YYYY}-{NNNN}. Usa {YYYY}, {NNNN}, {MM}, {DD}',
            ],
            'prefijo_proyecto' => [
                'type' => 'text',
                'validation' => 'prefijo_10',
                'help' => 'Solo letras y números (ej: PR, OBRA). Máx. 10 caracteres.',
            ],

            // ──────────────────────────────────────────────────────────────
            // TOKENS Y EXPIRACIÓN
            // ──────────────────────────────────────────────────────────────
            'token_caducidad_dias' => [
                'type' => 'number',
                'validation' => 'token_dias',
                'help' => 'Días antes de que expire un enlace de firma.',
            ],

            // ──────────────────────────────────────────────────────────────
            // LÍMITES DE ARCHIVOS
            // ──────────────────────────────────────────────────────────────
            'archivo_tamano_max_mb' => [
                'type' => 'select',
                'validation' => 'archivo_size',
                'help' => 'Tamaño máximo de cada archivo adjunto.',
                'options' => [2, 5, 10, 20, 50],
            ],
            'archivo_cantidad_max' => [
                'type' => 'number',
                'validation' => 'archivo_count',
                'help' => 'Cantidad máxima de archivos por formulario.',
            ],
        ];
    }

    /**
     * Genera las reglas de validación de Laravel.
     *
     * @return array Array de reglas en formato ['field' => [rules...]]
     */
    public static function getValidationRules(): array
    {
        $rules = [];
        $templates = self::validationTemplates();

        foreach (self::config() as $field => $config) {
            $templateName = $config['validation'];
            $rules[$field] = $templates[$templateName];
        }

        return $rules;
    }

    /**
     * Obtiene la configuración de un campo específico.
     *
     * @param string $name Nombre del campo
     * @return array|null Array con tipo, validación, ayuda, etc. o null si no existe
     */
    public static function getField(string $name): ?array
    {
        return self::config()[$name] ?? null;
    }

    /**
     * Obtiene el maxlength de un campo si aplica.
     *
     * @param string $name Nombre del campo
     * @return int|null Máximo de caracteres o null
     */
    public static function getMaxLength(string $name): ?int
    {
        $field = self::getField($name);
        if (!$field) {
            return null;
        }

        $validation = $field['validation'];
        $template = self::validationTemplates()[$validation] ?? [];

        foreach ($template as $rule) {
            if (strpos($rule, 'max:') === 0) {
                return (int) str_replace('max:', '', $rule);
            }
        }

        return null;
    }
}
