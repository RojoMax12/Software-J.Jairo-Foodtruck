<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bodega extends Model
{
    use HasFactory;

    protected $table = 'bodegas';

    protected $fillable = [
        'nombre_bodega',
        'ubicacion_bodega',
        'cantidad_productos'
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_bodega');
    }

}
