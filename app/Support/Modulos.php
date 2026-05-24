<?php

namespace App\Support;

use App\Models\Empresa;

/**
 * Helper estático para consultar qué módulos opcionales están activos.
 *
 * El resultado se cachea en memoria durante la request para evitar
 * múltiples queries a la misma fila de `empresa`.
 * Llamar a limpiarCache() tras cambiar el flag en BD.
 */
class Modulos
{
    private static ?bool $materialesAvanzadoCache = null;

    public static function materialesAvanzado(): bool
    {
        if (static::$materialesAvanzadoCache === null) {
            static::$materialesAvanzadoCache = (bool) (Empresa::actual()->modulo_materiales_avanzado ?? true);
        }

        return static::$materialesAvanzadoCache;
    }

    public static function limpiarCache(): void
    {
        static::$materialesAvanzadoCache = null;
    }
}
