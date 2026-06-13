<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'precio',
        'tipo',
    ];

    protected $casts = [
        'precio' => 'integer',
    ];

    // Un producto pertenece a muchos pedidos (many-to-many)
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'producto_pedido', 'id_producto', 'id_pedido');
    }

    // Un producto tiene mmuchos producto_ingrediente (one-to-many)
    public function producto_ingrediente()
    {
        return $this->hasMany(Producto_ingrediente::class, 'id_producto');
    }

    // Un producto aparece en muchas relaciones oferta_producto
    public function ofertaProductos()
    {
        return $this->hasMany(OfertaProducto::class, 'id_productos');
    }
}