<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertas';

    protected $fillable = [
        'descripcion',
        'precio_oferta',
        'fecha',
        'dia_semana',
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