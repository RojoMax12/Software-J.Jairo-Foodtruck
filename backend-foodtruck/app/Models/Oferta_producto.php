<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfertaProducto extends Model
{
    protected $table = 'oferta_producto';

    protected $fillable = [
        'id_productos',
        'descripcion',
        'precio_oferta',
        'tipo',
    ];

    protected $casts = [
        'precio_oferta' => 'integer',
    ];

    // Pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_productos');
    }

    // Pertenece a una oferta
    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_ofertas');
    }
}