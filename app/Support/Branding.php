<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Centraliza la lógica de marca (logo + nombre + colores) leyendo de la
 * tabla `empresa` con caché de request para evitar consultas
 * duplicadas en una misma petición.
 *
 * Cascada:
 *  1. Valores guardados en `empresa` (editables desde UI).
 *  2. Asset ENIA por defecto (img/enia-logo.png) si está presente.
 *  3. Texto fallback (abreviatura) si no hay imagen.
 */
class Branding
{
    private static ?Empresa $cache = null;

    private static bool $cacheCargada = false;

    public static function actual(): ?Empresa
    {
        if (self::$cacheCargada) {
            return self::$cache;
        }

        self::$cacheCargada = true;

        try {
            if (! Schema::hasTable('empresa')) {
                return self::$cache = null;
            }

            self::$cache = Empresa::query()->first();
        } catch (Throwable) {
            self::$cache = null;
        }

        return self::$cache;
    }

    public static function limpiarCache(): void
    {
        self::$cache = null;
        self::$cacheCargada = false;
    }

    public static function logoUrl(): ?string
    {
        $logo = self::actual()?->logoUrl();

        if ($logo !== null) {
            return $logo;
        }

        if (file_exists(public_path('img/enia-logo.png'))) {
            return asset('img/enia-logo.png');
        }

        return null;
    }

    public static function nombre(): string
    {
        $config = self::actual();

        return $config?->nombre_comercial
            ?: $config?->nombre
            ?: 'ELECIND';
    }

    public static function abreviatura(): string
    {
        return mb_strtoupper(mb_substr(self::nombre(), 0, 1));
    }

    public static function tieneLogo(): bool
    {
        return self::logoUrl() !== null;
    }

    public static function colorPrimario(): string
    {
        return self::actual()?->color_primario ?? '#871f1f';
    }

    public static function colorSecundario(): string
    {
        return self::actual()?->color_secundario ?? '#f5e6e6';
    }
}
