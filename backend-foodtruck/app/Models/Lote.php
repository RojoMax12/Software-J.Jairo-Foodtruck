<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'id_producto',
        'id_stock',
        'id_bodega',
        'cantidad_producto',
        'fecha_vencimiento',
        'fecha_emision'
    ];

    protected $casts = [
        'cantidad_producto' => 'integer',
        'fecha_vencimiento' => 'date',
        'fecha_emision' => 'date',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'id_stock');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'id_bodega');
    }

}
