<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'id_pedido',
        'id_caja',
    ];

    // Una venta pertenece a un pedido
    public function pedido()
    {
        return $this->hasOne(Pedido::class, 'id_pedido');
    }

    // Una venta pertenece a una caja
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    
}