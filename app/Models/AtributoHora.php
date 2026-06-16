<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de atributos imputables en partes y albaranes.
 *
 * Los 11 atributos están fijos por código (sembrados con AtributosHoraSeeder).
 * No se editan desde UI.
 */
class AtributoHora extends Model
{
    protected $table = 'atributos_hora';

    public const GRUPO_NORMAL = 'normal';

    public const GRUPO_EXTRA = 'extra';

    public const GRUPO_PLUS = 'plus';

    public const COD_LABOR = 'labor';

    public const COD_LAB_NOCHE = 'lab_noche';

    public const COD_FEST = 'fest';

    public const COD_FEST_NOCT = 'fest_noct';

    public const COD_EX_LAB = 'ex_lab';

    public const COD_EX_LAB_NOC = 'ex_lab_noc';

    public const COD_EX_FES = 'ex_fes';

    public const COD_EX_FES_NOCT = 'ex_fes_noct';

    public const COD_PLUS_RETEN = 'plus_reten';

    public const COD_PLUS_FESTIVO = 'plus_festivo';

    public const COD_PLUS_NOCHE = 'plus_noche';

    protected $fillable = [
        'codigo',
        'nombre_corto',
        'nombre_largo',
        'grupo',
        'mapeo_tasa',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function tarifasCliente(): HasMany
    {
        return $this->hasMany(TarifaCliente::class, 'atributo_id');
    }

    /* ── Scopes ──────────────────────────────────────────────────────── */

    public function scopeNormales(Builder $q): Builder
    {
        return $q->where('grupo', self::GRUPO_NORMAL);
    }

    public function scopeExtras(Builder $q): Builder
    {
        return $q->where('grupo', self::GRUPO_EXTRA);
    }

    public function scopePluses(Builder $q): Builder
    {
        return $q->where('grupo', self::GRUPO_PLUS);
    }

    /** Tipos de hora: normales + extras (lo que se paga al trabajador por hora). */
    public function scopeHoras(Builder $q): Builder
    {
        return $q->whereIn('grupo', [self::GRUPO_NORMAL, self::GRUPO_EXTRA]);
    }

    public function esPlus(): bool
    {
        return $this->grupo === self::GRUPO_PLUS;
    }

    public function esHora(): bool
    {
        return in_array($this->grupo, [self::GRUPO_NORMAL, self::GRUPO_EXTRA], true);
    }

    /**
     * Mapeo inverso: dado un nombre de campo de users (tasa_hora, tasa_extra, ...),
     * devuelve el atributo correspondiente. Lo usa el Observer de users para saber
     * qué atributo se ha cambiado.
     */
    public static function porMapeoTasa(string $campo): ?self
    {
        return self::query()->where('mapeo_tasa', $campo)->first();
    }
}
