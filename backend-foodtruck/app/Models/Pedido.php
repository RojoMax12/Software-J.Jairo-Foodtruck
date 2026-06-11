<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'id_cotizacion',
        'id_estado_pedido',
        'id_usuario_dicreme',
        'id_usuario_distribuidor',
        'fecha_creacion',
        'hora_creacion',
        'monto_estimado',
        'monto_final'
        ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'hora_creacion' => 'datetime',
    ];


    public function usuarioDicreme(): BelongsTo
    {
        return $this->belongsTo(Usuario_dicreme::class, 'id_usuario_dicreme');
    }

    public function estadoPedido(): HasOne
    {
        return $this->HasOne(Estado_pedido::class, 'id_estado_pedido');
    }

    public function despacho(): HasOne
    {
        return $this->hasOne(Despacho::class, 'id_pedido');
    }

    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class, 'id_pedido');
    }

    public function pedidoProductos(): HasMany
    {
        return $this->hasMany(Pedido_producto::class, 'id_pedido');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }
}