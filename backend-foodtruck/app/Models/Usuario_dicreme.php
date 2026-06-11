<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario_dicreme extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios_dicreme';

    protected $fillable = [
        'nombre_usuario',
        'correo_electronico',
        'contrasena',
        'id_rol'
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