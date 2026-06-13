<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $table = 'ofertas';

    protected $fillable = [
        'id_productos',
        'descripcion',
        'precio_oferta',
        'tipo',
        'fecha',
    ];

    protected $casts = [
        'precio_oferta' => 'decimal:2',
        'fecha'         => 'datetime',
    ];

    // Una oferta tiene muchas oferta_producto (one-to-many)
    public function oferta_producto()
    {
        return $this->hasMany(OfertaProducto::class, 'id_ofertas');
    }
}