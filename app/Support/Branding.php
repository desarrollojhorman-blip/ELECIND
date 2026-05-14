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
 * Cascada del logo:
 *  1. Valores guardados en `empresa` (editables desde UI).
 *  2. Asset ENIA por defecto (images/brand/enia.svg) si está presente.
 *  3. Texto fallback (abreviatura) si no hay imagen.
 *
 * Ratio aspect:
 *  - Cuadrado:    0.85 ≤ ratio ≤ 1.15 → encaja bien en cuadrados (sidebar colapsado).
 *  - Rectangular: ratio fuera de ese rango → se usa abreviatura en sidebar colapsado.
 */
class Branding
{
    private const RATIO_CUADRADO_MIN = 0.85;

    private const RATIO_CUADRADO_MAX = 1.15;

    private const RATIO_ENIA_DEFAULT = 1.0;

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

        if (file_exists(public_path('images/brand/enia.svg'))) {
            return asset('images/brand/enia.svg');
        }

        return null;
    }

    /**
     * Logo específico para albaranes/facturas. Si no hay uno definido,
     * cae al logo principal (incluida la cascada ENIA por defecto).
     */
    public static function logoAlbaranUrl(): ?string
    {
        $logo = self::actual()?->logoAlbaranUrl();

        if ($logo !== null) {
            return $logo;
        }

        return self::logoUrl();
    }

    public static function logoRatio(): ?float
    {
        $empresa = self::actual();

        if ($empresa?->logo_path !== null && $empresa?->logo_path !== '') {
            return $empresa->logo_ratio;
        }

        return self::RATIO_ENIA_DEFAULT;
    }

    public static function logoAlbaranRatio(): ?float
    {
        $empresa = self::actual();

        if ($empresa?->logo_albaran_path !== null && $empresa?->logo_albaran_path !== '') {
            return $empresa->logo_albaran_ratio;
        }

        return self::logoRatio();
    }

    public static function logoZoom(): int
    {
        return self::actual()?->logo_zoom ?: 100;
    }

    public static function logoAlbaranZoom(): int
    {
        return self::actual()?->logo_albaran_zoom ?: 100;
    }

    public static function logoEsCuadrado(): bool
    {
        return self::ratioEsCuadrado(self::logoRatio());
    }

    public static function logoAlbaranEsCuadrado(): bool
    {
        return self::ratioEsCuadrado(self::logoAlbaranRatio());
    }

    public static function ratioEsCuadrado(?float $ratio): bool
    {
        if ($ratio === null) {
            return true;
        }

        return $ratio >= self::RATIO_CUADRADO_MIN && $ratio <= self::RATIO_CUADRADO_MAX;
    }

    /**
     * Detecta el ratio (ancho / alto) de una imagen leída desde disco.
     * Soporta formatos raster (PNG/JPG/WebP) vía getimagesize y SVG
     * parseando viewBox o atributos width/height.
     */
    public static function detectarRatio(string $absolutePath): ?float
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        if (str_ends_with(strtolower($absolutePath), '.svg')) {
            return self::detectarRatioSvg($absolutePath);
        }

        $info = @getimagesize($absolutePath);

        if ($info === false || (int) $info[1] === 0) {
            return null;
        }

        return (float) $info[0] / (float) $info[1];
    }

    private static function detectarRatioSvg(string $path): ?float
    {
        $contenido = @file_get_contents($path);

        if ($contenido === false) {
            return null;
        }

        $usoErroresPrevio = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($contenido);

            if ($xml === false) {
                return null;
            }

            $attrs = $xml->attributes();

            if (isset($attrs['viewBox'])) {
                $partes = preg_split('/[\s,]+/', trim((string) $attrs['viewBox']));

                if (is_array($partes) && count($partes) === 4) {
                    $w = (float) $partes[2];
                    $h = (float) $partes[3];

                    if ($h > 0) {
                        return $w / $h;
                    }
                }
            }

            if (isset($attrs['width'], $attrs['height'])) {
                $w = (float) $attrs['width'];
                $h = (float) $attrs['height'];

                if ($h > 0) {
                    return $w / $h;
                }
            }

            return null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($usoErroresPrevio);
        }
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
