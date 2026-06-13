<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja';

    protected $fillable = [
        'id_venta',
        'id_usuario',
        'fecha_apertura',
        'total_ventas',
        'total_recaudado',
    ];

    protected $casts = [
        'fecha_apertura'   => 'datetime',
        'total_ventas'     => 'integer',
        'total_recaudado'  => 'integer',
    ];

    // Una caja pertenece a una venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    // Una caja pertenece a un usuario (solo admin)
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}