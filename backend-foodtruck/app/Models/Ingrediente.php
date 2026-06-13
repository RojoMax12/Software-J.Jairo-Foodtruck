<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingrediente extends Model
{
    protected $table = 'ingredientes';

    protected $fillable = [
        'nombre',
        'cantidad',
        'fecha_de_ingreso',
        'disponible',
    ];

    protected $casts = [
        'cantidad'         => 'integer',
        'fecha_de_ingreso' => 'datetime',
        'disponible'       => 'boolean',
    ];

    // Un ingrediente pertenece a muchos producto_ingrediente (one-to-many)
    public function producto_ingrediente()
    {
        return $this->hasMany(Producto_ingrediente::class, 'id_ingrediente');
    }
}