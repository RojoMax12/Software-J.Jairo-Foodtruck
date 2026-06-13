<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    protected $table = 'estado_pedido';

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'nombre',
    ];

    // Un estado tiene muchos pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_estado_pedido', 'id_pedido');
    }
}