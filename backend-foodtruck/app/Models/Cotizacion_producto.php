<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotizacion_producto extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_producto';

    protected $fillable = [
        'id_cotizacion',
        'id_producto',
        'cantidad',
        'precio_unitario_venta'
    ];

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

}