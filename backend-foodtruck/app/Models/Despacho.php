<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Despacho extends Model
{
    use HasFactory;

    protected $table = 'despachos';
    
    protected $fillable = [
        'id_pedido',
        'direccion_entrega',
        'comuna',
        'fecha_entrega',
        'persona_recibe',
        'estado_despacho'
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    public function pedido(): HasOne
    {
        return $this->hasOne(Pedido::class, 'id_pedido');
    }

}


