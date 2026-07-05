<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto_Tamaño extends Model
{
    use HasFactory;

    protected $table = 'producto_tamaño';

    protected $fillable = [
        'id_producto',
        'id_tamaño',
        'precio',
    ];

    // Pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    // Pertenece a un tamaño
    public function tamaño()
    {
        return $this->belongsTo(Tamaño::class, 'id_tamaño');
    }
}