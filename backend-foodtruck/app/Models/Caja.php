<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'caja';

    protected $fillable = [
        'id_usuario',
        'fecha_apertura',
        'monto_inicial',
        'total_ventas',
        'total_recaudado',
    ];

    protected $casts = [
        'fecha_apertura'   => 'datetime',
        'total_ventas'     => 'integer',
        'total_recaudado'  => 'integer',
    ];

    // Una caja tiene muchas ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_caja');
    }

    // Una caja pertenece a un usuario (solo admin)
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}