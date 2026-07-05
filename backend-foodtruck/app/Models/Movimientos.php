<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimientos extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'id_ingrediente',
        'cantidad',
        'tipo_movimiento',
        'fecha_movimiento',
    ];

    // Pertenece a un ingrediente
    public function ingrediente()
    {
        return $this->belongsTo(Ingrediente::class, 'id_ingrediente');
    }
}