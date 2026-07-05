<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_pedido_ingrediente extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido_ingrediente';

    protected $fillable = [
        'id_detalle_pedido',
        'id_ingrediente',
        'tipo_modificacion',
        'precio_aplicado',
    ];

    public function detalle_pedido()
    {
        return $this->belongsTo(Detalle_Pedido::class, 'id_detalle_pedido');
    }

    public function ingrediente()
    {
        return $this->belongsTo(Ingrediente::class, 'id_ingrediente');
    }
}