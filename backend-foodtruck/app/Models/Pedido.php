<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'id_estado_pedido',
        'id_usuario',
        'id_estado_pago',
        'numero_pedido_dia',
        'nombre_persona',
        'numero_telefono',
        'metodo_pago',
        'fecha_de_pago',
        'fecha',
        'total',
        'notas'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total'  => 'integer',
    ];

    // Un pedido pertenece a un estado
    public function estadoPedido()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_pedido', 'id_pedido');
    }

    public function estadoPago()
    {
        return $this->belongsTo(EstadoPago::class, 'id_estado_pago', 'id_pedido');
    }

    // Un pedido pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // Un pedido tiene una venta
    public function venta()
    {
        return $this->hasOne(Venta::class, 'id_pedido');
    }

    // Un pedido tiene muchos producto_pedido (one-to-many)
    public function producto_pedido()
    {
        return $this->hasMany(Producto_Pedido::class, 'id_pedido');
    }
}