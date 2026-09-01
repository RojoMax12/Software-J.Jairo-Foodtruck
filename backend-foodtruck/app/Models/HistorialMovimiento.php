<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMovimiento extends Model
{
    use HasFactory;

    protected $table = 'historial_movimientos';

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'tipo',
        'accion',
        'descripcion',
        'entidad',
        'detalle',
        'usuario',
        'id_usuario',
        'monto',
        'fecha',
    ];

    protected $casts = [
        'monto' => 'float',
        'fecha' => 'datetime',
    ];

    public function usuarioRel()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}