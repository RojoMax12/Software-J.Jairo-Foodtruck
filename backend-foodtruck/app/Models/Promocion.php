<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';
    protected $primaryKey = 'id_promocion';

    protected $fillable = [
        'id_producto',
        'id_tamaño',
        'titulo',
        'descripcion',
        'precio_promocional',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'id_tamaño' => 'integer',
        'precio_promocional' => 'integer',
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function tamano()
    {
        return $this->belongsTo(Tamaño::class, 'id_tamaño', 'id_tamaño');
    }
}