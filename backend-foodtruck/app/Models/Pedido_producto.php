<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido_producto extends Model
{
    use HasFactory;

    protected $table = 'pedido_producto';

    protected $fillable = [
        'id_pedido',
        'id_producto',
        'precio_unitario_venta',
        'cantidad'
    ];

    protected $casts = [
        'precio_unitario_venta' => 'integer', # Esto debemos hashearlo, lo dejaré en integer por el momento
        'cantidad' => 'integer',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

}