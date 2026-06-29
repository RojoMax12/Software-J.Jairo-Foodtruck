<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class producto_pedido extends Model
{
    protected $table = 'producto_pedido';

    protected $fillable = [
        'id_producto',
        'id_pedido',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
}


