<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tamaño extends Model
{
    use HasFactory;

    protected $table = 'tamaños';

    protected $primaryKey = 'id_tamaño';

    public $incrementing = true;

    protected $fillable = [
        'nombre',
    ];

    // Un tamaño tiene muchos productos
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_tamaño', 'id_producto');
    }
}