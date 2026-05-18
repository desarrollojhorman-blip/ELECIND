<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Centraliza la lógica de marca (logo + nombre + colores) leyendo de la
 * tabla `empresa` con caché de request para evitar consultas duplicadas.
 *
 * Cascada del logo para la UI (login, sidebar, móvil):
 *  1. logo_app     (Ajustes)   → prioridad absoluta.
 *  2. logo_path    (Empresa)   → fallback si no hay logo_app.
 *  3. enia.svg     (asset)     → fallback si no hay logo de empresa.
 *  4. null         (texto)     → usar Branding::nombre().
 *
 * Cascada del logo para documentos (albaranes/PDF):
 *  1. logo_albaran_path (Empresa) → logo específico para documentos.
 *  2. logo_path         (Empresa) → fallback.
 *  3. enia.svg          (asset)   → fallback final.
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

    // ── Logo UI (login, sidebar, móvil) ─────────────────────────────────────

    public static function logoUrl(): ?string
    {
        // 1. Logo app (Ajustes) — prioridad absoluta
        $logoApp = self::actual()?->logoAppUrl();
        if ($logoApp !== null) {
            return $logoApp;
        }

        // 2. Logo empresa
        $logoEmpresa = self::actual()?->logoUrl();
        if ($logoEmpresa !== null) {
            return $logoEmpresa;
        }

        // 3. Asset ENIA por defecto
        if (file_exists(public_path('images/brand/enia.svg'))) {
            return asset('images/brand/enia.svg');
        }

        return null;
    }

    public static function logoRatio(): ?float
    {
        $empresa = self::actual();

        if ($empresa?->logo_app_path !== null && $empresa?->logo_app_path !== '') {
            return $empresa->logo_app_ratio;
        }

        if ($empresa?->logo_path !== null && $empresa?->logo_path !== '') {
            return $empresa->logo_ratio;
        }

        return self::RATIO_ENIA_DEFAULT;
    }

    public static function logoZoom(): int
    {
        $empresa = self::actual();

        if ($empresa?->logo_app_path !== null && $empresa?->logo_app_path !== '') {
            return $empresa->logo_app_zoom ?: 100;
        }

        return $empresa?->logo_zoom ?: 100;
    }

    public static function logoEsCuadrado(): bool
    {
        return self::ratioEsCuadrado(self::logoRatio());
    }

    // ── Logo documentos (albaranes/PDF) ────────────────────────────────────

    public static function logoAlbaranUrl(): ?string
    {
        $logo = self::actual()?->logoAlbaranUrl();

        if ($logo !== null) {
            return $logo;
        }

        // Cae al logo de empresa (no al logo_app — los documentos no usan el logo de la app)
        $logoEmpresa = self::actual()?->logoUrl();
        if ($logoEmpresa !== null) {
            return $logoEmpresa;
        }

        if (file_exists(public_path('images/brand/enia.svg'))) {
            return asset('images/brand/enia.svg');
        }

        return null;
    }

    public static function logoAlbaranRatio(): ?float
    {
        $empresa = self::actual();

        if ($empresa?->logo_albaran_path !== null && $empresa?->logo_albaran_path !== '') {
            return $empresa->logo_albaran_ratio;
        }

        if ($empresa?->logo_path !== null && $empresa?->logo_path !== '') {
            return $empresa->logo_ratio;
        }

        return self::RATIO_ENIA_DEFAULT;
    }

    public static function logoAlbaranZoom(): int
    {
        return self::actual()?->logo_albaran_zoom ?: 100;
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

    // ── Nombre y texto ──────────────────────────────────────────────────────

    public static function nombre(): string
    {
        $config = self::actual();

        return $config?->nombre_comercial
            ?: $config?->nombre
            ?: 'ENIA';
    }

    public static function abreviatura(): string
    {
        return mb_strtoupper(mb_substr(self::nombre(), 0, 1));
    }

    public static function tieneLogo(): bool
    {
        return self::logoUrl() !== null;
    }

    // ── Colores ─────────────────────────────────────────────────────────────

    public static function colorPrimario(): string
    {
        return self::actual()?->color_primario ?? '#334155';
    }

    public static function colorSecundario(): string
    {
        return self::actual()?->color_secundario ?? '#f1f5f9';
    }

    public static function colorTextoEncabezado(): string
    {
        return self::actual()?->color_texto_encabezado ?? '#ffffff';
    }
}
