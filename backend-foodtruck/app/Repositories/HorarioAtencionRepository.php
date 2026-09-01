<?php

namespace App\Repositories;
use App\Models\Horario_atencion;

class HorarioAtencionRepository
{
    public function getAllHorarioAtencion()
    {
        return Horario_atencion::orderBy('dia_semana', 'asc')->get();
    }

    public function createHorarioAtencion($data)
    {
        return Horario_atencion::create($data);
    }

    public function getHorarioAtencionById($id_horario_atencion)
    {
        return Horario_atencion::find($id_horario_atencion);
    }

    public function updateHorarioAtencion($id_horario_atencion, $data)
    {
        $horarioAtencion = Horario_atencion::find($id_horario_atencion);
        if ($horarioAtencion) {
            $horarioAtencion->update($data);
            return $horarioAtencion;
        }
        return null;
    }

    public function deleteHorarioAtencionById($id_horario_atencion)
    {
        $horarioAtencion = Horario_atencion::find($id_horario_atencion);
        if ($horarioAtencion) {
            $horarioAtencion->delete();
            return true;
        }
        return false;
    }
}