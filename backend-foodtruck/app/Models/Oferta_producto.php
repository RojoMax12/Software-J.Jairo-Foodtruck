<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaProducto extends Model
{
    use HasFactory;

    protected $table = 'oferta_producto';

    protected $fillable = [
        'id_productos',
        'id_ofertas',
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