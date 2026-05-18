<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $albaran_id
 * @property string $nombre
 * @property string $ruta
 * @property string $nombre_original
 * @property string|null $mime_type
 * @property int $tamano
 * @property int $subido_por
 */
class AlbaranArchivo extends Model
{
    protected $table = 'albaran_archivos';

    protected $fillable = [
        'albaran_id',
        'nombre',
        'ruta',
        'nombre_original',
        'mime_type',
        'tamano',
        'subido_por',
    ];

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->ruta);
    }

    public function tamanoFormateado(): string
    {
        $bytes = $this->tamano;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
