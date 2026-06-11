<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';

    protected $fillable = [
        'nombre_rol'
    ];

    public function usuariosDicreme(): HasMany
    {
        return $this->hasMany(Usuario_dicreme::class, 'id_rol');
    }

    public function usuariosDistribuidores(): HasMany
    {
        return $this->hasMany(Usuario_distribuidores::class, 'id_rol');
    }

}