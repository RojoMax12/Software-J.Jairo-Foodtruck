<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'id_usuario_dicreme',
        'id_distribuidor',
        'id_estado_cotizacion',
        'persona_recibe',
        'fecha_creacion',
        'hora_creacion',
        'total_cotizacion',
        'subtotal_cotizacion',
        'tipo_descuento_general',
        'valor_descuento_general',
        'descuento_general_aplicado',
        'descuento_productos_total'
    ];

    public function usuarioDicreme(): BelongsTo
    {
        return $this->belongsTo(Usuario_dicreme::class, 'id_usuario_dicreme');
    }

    public function distribuidor(): BelongsTo
    {
        return $this->belongsTo(Usuario_distribuidores::class, 'id_distribuidor');
    }

    public function cotizacionProductos(): HasMany
    {
        return $this->hasMany(Cotizacion_producto::class, 'id_cotizacion');
    }

    public function pedidos(): HasOne
    {
        return $this->hasOne(Pedido::class, 'id_cotizacion');
    }

    public function estadoCotizacion(): HasOne
    {
        return $this->hasOne(Estado_cotizacion::class, 'id_estado_cotizacion');
    }
}