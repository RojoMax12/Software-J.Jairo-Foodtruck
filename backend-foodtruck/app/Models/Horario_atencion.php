<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario_atencion extends Model
{
    use HasFactory;

    protected $table = 'horario_atencion';

    protected $primaryKey = 'id_horario_atencion';

    protected $fillable = [
        'dia_semana',
        'hora_apertura',
        'hora_cierre',
        'minuto_colchon',
        'activo',
        'id_usuario',
    ];

    public function usuario_modificacion()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}