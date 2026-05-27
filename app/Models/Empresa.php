<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Empresa extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('empresa')
            ->logOnly([
                'nombre', 'nombre_comercial', 'cif', 'direccion', 'codigo_postal', 'poblacion',
                'provincia', 'telefono', 'email_contacto', 'email_notificaciones',
                'color_primario', 'color_secundario', 'color_texto_encabezado',
                'plantilla_numeracion_albaran', 'plantilla_numeracion_cliente', 'prefijo_proyecto',
                'token_caducidad_dias', 'modulo_materiales_avanzado',
                'archivo_tamano_max_mb', 'archivo_cantidad_max',
                'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_from_address', 'mail_from_name',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => 'Configuración empresa');
    }


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
        'logo_app_path',
        'logo_app_ratio',
        'logo_app_zoom',
        'color_primario',
        'color_secundario',
        'color_texto_encabezado',
        'plantilla_numeracion_albaran',
        'plantilla_numeracion_cliente',
        'prefijo_proyecto',
        'token_caducidad_dias',
        'plantilla_pdf_config',
        'archivo_tamano_max_mb',
        'archivo_cantidad_max',
        'modulo_materiales_avanzado',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
    ];

    protected function casts(): array
    {
        return [
            'plantilla_pdf_config' => 'array',
            'token_caducidad_dias' => 'integer',
            'archivo_tamano_max_mb' => 'integer',
            'archivo_cantidad_max' => 'integer',
            'logo_ratio' => 'float',
            'logo_zoom' => 'integer',
            'logo_albaran_ratio' => 'float',
            'logo_albaran_zoom' => 'integer',
            'logo_app_ratio' => 'float',
            'logo_app_zoom' => 'integer',
            'modulo_materiales_avanzado' => 'boolean',
            'mail_port' => 'integer',
            'mail_password' => 'encrypted',
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
                'nombre' => 'ENIA',
                'color_primario' => '#334155',
                'color_secundario' => '#f1f5f9',
                'color_texto_encabezado' => '#ffffff',
                'plantilla_numeracion_albaran' => 'ALB-{YYYY}-{NNNN}',
                'token_caducidad_dias' => 7,
                'modulo_materiales_avanzado' => true,
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

    public function logoAppUrl(): ?string
    {
        if ($this->logo_app_path === null || $this->logo_app_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->logo_app_path);
    }
}
