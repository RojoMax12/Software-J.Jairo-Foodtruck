<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario_distribuidores extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios_distribuidores';

    protected $fillable = [
        'id_rol',
        'rut_empresa',
        'nombre_empresa',
        'contrasena',
        'direccion',
        'telefono',
        'correo_electronico',
        'comuna'
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}