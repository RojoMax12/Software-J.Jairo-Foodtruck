<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta_producto extends Model
{
    use HasFactory;

    protected $table = 'oferta_producto';

    protected $fillable = [
        'id_oferta',
        'id_productos',
        'id_tamaño',
        'descripcion',
        'precio_oferta',
        'tipo',
    ];

    // Pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_productos', 'id_producto');
    }

    // Tamaño al que aplica la oferta (nulo = todos los tamaños)
    public function tamano()
    {
        return $this->belongsTo(Tamaño::class, 'id_tamaño', 'id_tamaño');
    }

    // Pertenece a una oferta
    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }
}