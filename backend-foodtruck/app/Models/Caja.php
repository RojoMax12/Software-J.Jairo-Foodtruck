<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'caja';

    protected $primaryKey = 'id_caja';

    protected $fillable = [
        'id_usuario',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'total_ventas',
        'total_recaudado',
        'estado',
        'efectivo_esperado',
        'diferencia',
        'ventas_efectivo',
        'ventas_debito',
        'ventas_transferencia',
        'gastos_efectivo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_apertura'       => 'datetime',
        'fecha_cierre'         => 'datetime',
        'monto_inicial'        => 'integer',
        'total_ventas'         => 'integer',
        'total_recaudado'      => 'integer',
        'efectivo_esperado'    => 'integer',
        'diferencia'           => 'integer',
        'ventas_efectivo'      => 'integer',
        'ventas_debito'        => 'integer',
        'ventas_transferencia' => 'integer',
        'gastos_efectivo'      => 'integer',
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

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}