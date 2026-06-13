<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto_ingrediente extends Model
{
    use HasFactory;

    protected $table = 'producto_ingrediente';

    protected $fillable = [
        'id_producto',
        'id_ingrediente',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'id_ingrediente');
    }
}