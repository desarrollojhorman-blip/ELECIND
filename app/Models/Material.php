<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Material extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'materiales';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('material')
            ->logOnly(['descripcion', 'unidad_medida', 'stock', 'precio_coste', 'precio_venta', 'activo', 'numero_pedido_id', 'familia_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Material: {$this->descripcion}");
    }

    protected $fillable = [
        'numero_pedido_id',
        'familia_id',
        'descripcion',
        'unidad_medida',
        'stock',
        'precio_coste',
        'precio_venta',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:2',
            'precio_coste' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'activo' => 'boolean',
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

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'material_proyecto')->withTimestamps();
    }
}
