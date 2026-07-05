<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertas';

    protected $primaryKey = 'id_oferta';

    protected $fillable = [
        'descripcion',
        'precio_oferta',
        'fecha',
        'dia_semana',
    ];

    protected $casts = [
        'precio_oferta' => 'integer',
        'fecha'         => 'datetime',
    ];

    // Una oferta tiene muchas oferta_producto (one-to-many)
    public function oferta_producto()
    {
        return $this->hasMany(OfertaProducto::class, 'id_ofertas');
    }
}