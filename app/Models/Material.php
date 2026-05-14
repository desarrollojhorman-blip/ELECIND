<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiales';

    protected $fillable = [
        'numero_pedido_id',
        'familia_id',
        'descripcion',
        'unidad_medida',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:2',
        ];
    }

    public function numeroPedido(): BelongsTo
    {
        return $this->belongsTo(NumeroPedido::class);
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(FamiliaMaterial::class, 'familia_id');
    }

    public function lineasAlbaran(): HasMany
    {
        return $this->hasMany(AlbaranLineaMaterial::class);
    }
}
