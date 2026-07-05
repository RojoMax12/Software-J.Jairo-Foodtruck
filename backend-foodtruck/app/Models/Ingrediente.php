<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingrediente extends Model
{
    use HasFactory;

    protected $table = 'ingredientes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad_actual',
        'cantidad_minima',
        'fecha_de_ingreso',
        'disponible',
    ];

    protected $casts = [
        'cantidad_actual'   => 'integer',
        'cantidad_minima'   => 'integer',
        'fecha_de_ingreso' => 'datetime',
        'disponible'       => 'boolean',
    ];

    // Un ingrediente pertenece a muchos producto_ingrediente (one-to-many)
    public function producto_ingrediente()
    {
        return $this->hasMany(Producto_ingrediente::class, 'id_ingrediente');
    }
}