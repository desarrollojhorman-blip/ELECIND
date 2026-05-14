<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $numero
 * @property string|null $descripcion
 * @property Carbon $fecha
 * @property string|null $proveedor
 */
class NumeroPedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'numero_pedidos';

    protected $fillable = [
        'numero',
        'descripcion',
        'fecha',
        'proveedor',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
