<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'id_cotizacion',
        'numero_factura',
        'fecha_venta',
        'glosa',
        'estado_pago',
        'monto_total'
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'monto_total' => 'integer',
    ];

    public function cotizacion(): HasOne
    {
        return $this->hasOne(Cotizacion::class, 'id_cotizacion');
    }

}