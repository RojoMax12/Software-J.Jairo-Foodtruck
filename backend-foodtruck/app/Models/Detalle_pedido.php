<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_Pedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';

    protected $primaryKey = 'id_detalle_pedido';

    protected $fillable = [
        'id_producto',
        'id_pedido',
        'id_tamaño',
        'cantidad',
        'precio_unitario',

    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function tamaño()
    {
        return $this->belongsTo(Tamaño::class, 'id_tamaño');
    }
}


