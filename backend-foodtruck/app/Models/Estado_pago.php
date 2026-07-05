<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Estado_pago extends Model
{
    protected $table = 'estado_pago';

    protected $primaryKey = 'id_estado_pago';

    public $incrementing = true;

    protected $fillable = [
        'nombre',
    ];

    // Un estado tiene muchos pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_estado_pago', 'id_pedido');
    }


}
