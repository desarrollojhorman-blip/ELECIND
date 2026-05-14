<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 */
class FamiliaMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'familias_material';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * @return HasMany<Material, $this>
     */
    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'familia_id');
    }
}
