<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'precio_ingrediente_extra',
        'tipo_armado',
        'cantidad_incluida',
        'id_categoria',
        'descripcion',
    ];

    protected $casts = [
        'precio_ingrediente_extra' => 'integer',
        'tipo_armado' => 'string',
        'cantidad_incluida' => 'integer',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function ingredientes()
    {
        return $this->hasMany(Producto_ingrediente::class, 'id_producto');
    }

    public function tamaños()
    {
        return $this->belongsToMany(Tamaño::class, 'producto_tamaño', 'id_producto', 'id_tamaño')
                    ->withPivot('precio');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_productos');
    }

    // Antes usaba belongsToMany(Pedido::class, 'producto_pedido', ...) hacia una
    // tabla que ya no existe. Un pedido se relaciona con el producto vía detalle_pedido.
    public function detalles()
    {
        return $this->hasMany(Detalle_Pedido::class, 'id_producto');
    }
}