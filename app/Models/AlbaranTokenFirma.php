<?php

namespace App\Models;

use App\Enums\TipoFirma;
use Database\Factories\AlbaranTokenFirmaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $albaran_id
 * @property TipoFirma $tipo_firmante
 * @property string $token
 * @property string $email_destino
 * @property string|null $nombre_destino
 * @property Carbon $caduca_at
 * @property Carbon|null $usado_at
 * @property Carbon|null $invalidado_at
 * @property int|null $reemplazado_por_token_id
 * @property int|null $generado_por_user_id
 */
class AlbaranTokenFirma extends Model
{
    /** @use HasFactory<AlbaranTokenFirmaFactory> */
    use HasFactory;

    protected $table = 'albaran_tokens_firma';

    protected $fillable = [
        'albaran_id',
        'tipo_firmante',
        'token',
        'email_destino',
        'nombre_destino',
        'caduca_at',
        'usado_at',
        'invalidado_at',
        'reemplazado_por_token_id',
        'generado_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo_firmante' => TipoFirma::class,
            'caduca_at' => 'datetime',
            'usado_at' => 'datetime',
            'invalidado_at' => 'datetime',
        ];
    }

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por_user_id');
    }

    public function reemplazadoPor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reemplazado_por_token_id');
    }

    public function firma(): HasOne
    {
        return $this->hasOne(AlbaranFirma::class, 'token_id');
    }

    /**
     * ¿El token está disponible para usarse?
     */
    public function esValido(): bool
    {
        return $this->usado_at === null
            && $this->invalidado_at === null
            && $this->caduca_at->isFuture();
    }
}
