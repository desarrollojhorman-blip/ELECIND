<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'nombre',
        'nombre_comercial',
        'cif',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'telefono',
        'email_contacto',
        'email_notificaciones',
        'logo_path',
        'logo_ratio',
        'logo_zoom',
        'logo_albaran_path',
        'logo_albaran_ratio',
        'logo_albaran_zoom',
        'color_primario',
        'color_secundario',
        'color_texto_encabezado',
        'plantilla_numeracion_albaran',
        'plantilla_numeracion_cliente',
        'token_caducidad_dias',
        'plantilla_pdf_config',
    ];

    protected function casts(): array
    {
        return [
            'plantilla_pdf_config' => 'array',
            'token_caducidad_dias' => 'integer',
            'logo_ratio' => 'float',
            'logo_zoom' => 'integer',
            'logo_albaran_ratio' => 'float',
            'logo_albaran_zoom' => 'integer',
        ];
    }

    /**
     * Devuelve la única fila de configuración. La crea con los defaults
     * de la migración si todavía no existe.
     */
    public static function actual(): self
    {
        $instancia = self::query()->first();

        if ($instancia === null) {
            $instancia = self::create([
                'nombre' => 'ELECIND',
                'color_primario' => '#871f1f',
                'color_secundario' => '#f5e6e6',
                'plantilla_numeracion_albaran' => 'ALB-{YYYY}-{NNNN}',
                'token_caducidad_dias' => 7,
            ]);
        }

        return $instancia;
    }

    public function logoUrl(): ?string
    {
        if ($this->logo_path === null || $this->logo_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function logoAlbaranUrl(): ?string
    {
        if ($this->logo_albaran_path === null || $this->logo_albaran_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->logo_albaran_path);
    }
}
