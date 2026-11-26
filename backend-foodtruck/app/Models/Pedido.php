<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'id_estado_pedido',
        'id_usuario',
        'id_estado_pago',
        'numero_pedido_dia',
        'tracking_token',
        'nombre_persona',
        'numero_telefono',
        'metodo_pago',
        'fecha_de_pago',
        'fecha',
        'total',
        'notas',
        'inventario_descontado'
    ];

    protected static function booted(): void
    {
        static::creating(function (Pedido $pedido) {
            if (empty($pedido->tracking_token)) {
                $pedido->tracking_token = Str::random(24);
            }
        });
    }

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_de_pago' => 'datetime',
        'total'  => 'integer',
        'inventario_descontado' => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format(\DateTimeInterface::ATOM);
    }

    // Un pedido pertenece a un estado
    public function estadoPedido()
    {
        return $this->belongsTo(Estado_pedido::class, 'id_estado_pedido', 'id_estado_pedido');
    }

    public function estadoPago()
    {
        return $this->belongsTo(Estado_pago::class, 'id_estado_pago', 'id_estado_pago');
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

    public function detalles()
    {
        return $this->hasMany(Detalle_pedido::class, 'id_pedido');
    }

}