<?php

namespace App\Support;

/**
 * Centraliza la lógica de marca (logo + nombre) con cascada:
 *  1. Logo/nombre del cliente (futuro: tabla configuracion_empresa).
 *  2. Logo/nombre ENIA por defecto (asset estático).
 *  3. Texto fallback "ENIA" si no hay imagen.
 *
 * Cuando se monte la pantalla "Configuración empresa" (Fase 1), basta
 * con cambiar las implementaciones aquí — el resto de la UI ya consulta
 * estos métodos.
 */
class Branding
{
    /**
     * URL pública de la imagen del logo a mostrar, o null si no hay
     * ninguna (entonces la UI debe pintar el fallback de texto).
     */
    public static function logoUrl(): ?string
    {
        // TODO Fase 1 — Configuración empresa: leer de configuracion_empresa->logo
        // Si esa tabla devuelve null, comprobar asset ENIA por defecto:
        // if (file_exists(public_path('img/enia-logo.png'))) {
        //     return asset('img/enia-logo.png');
        // }
        return null;
    }

    /**
     * Nombre de marca a mostrar (texto). Se usa como alt del logo o
     * como fallback visual cuando no hay imagen.
     */
    public static function nombre(): string
    {
        // TODO Fase 1 — leer configuracion_empresa->nombre_comercial
        return 'ENIA';
    }

    /**
     * Texto/letra corta para el sidebar colapsado (cuando no hay logo).
     */
    public static function abreviatura(): string
    {
        return mb_substr(self::nombre(), 0, 1);
    }

    public static function tieneLogo(): bool
    {
        return self::logoUrl() !== null;
    }
}
