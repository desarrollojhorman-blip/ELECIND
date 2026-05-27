<?php

namespace App\Models;

use App\Enums\TipoFirma;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $firmable_type
 * @property int $firmable_id
 * @property TipoFirma $tipo
 * @property int|null $firmado_por_user_id
 * @property int|null $token_id
 * @property string $firma_path
 * @property string|null $ip
 * @property string|null $user_agent
 * @property array<string, mixed>|null $geolocalizacion
 * @property Carbon $firmado_at
 */
class Firma extends Model
{
    protected $table = 'firmas';

    protected $fillable = [
        'firmable_type',
        'firmable_id',
        'tipo',
        'firmado_por_user_id',
        'token_id',
        'firma_path',
        'ip',
        'user_agent',
        'geolocalizacion',
        'firmado_at',
    ];

    protected function casts(): array
    {
        return [
            'tipo'           => TipoFirma::class,
            'geolocalizacion' => 'array',
            'firmado_at'     => 'datetime',
        ];
    }

    public function firmable(): MorphTo
    {
        return $this->morphTo();
    }

    public function firmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'firmado_por_user_id');
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(TokenFirma::class, 'token_id');
    }
}
