<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'id_pedido',
    ];

    // Una venta pertenece a un pedido
    public function pedido()
    {
        return $this->hasOne(Pedido::class, 'id_pedido');
    }

    // Una venta tiene muchos registros de caja
    public function cajas()
    {
        return $this->hasMany(Caja::class, 'id_venta');
    }
}