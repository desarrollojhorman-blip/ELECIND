<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApiToken extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('api_token')
            ->logOnly(['nombre', 'descripcion', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Token API: {$this->nombre}");
    }


    protected $table = 'api_tokens';

    protected $fillable = [
        'nombre',
        'descripcion',
        'token',
        'activo',
        'ultimo_uso_at',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'activo'        => 'boolean',
            'ultimo_uso_at' => 'datetime',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function tokenMascarado(): string
    {
        return substr($this->token, 0, 8).'••••••••'.substr($this->token, -4);
    }
}
