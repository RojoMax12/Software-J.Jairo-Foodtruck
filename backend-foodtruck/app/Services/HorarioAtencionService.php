<?php

namespace App\Services;
use App\Repositories\HorarioAtencionRepository;

class HorarioAtencionService
{
    protected $horarioAtencionRepository;

    public function __construct(HorarioAtencionRepository $horarioAtencionRepository)
    {
        $this->horarioAtencionRepository = $horarioAtencionRepository;
    }

    public function createHorarioAtencion($data)
    {
        return $this->horarioAtencionRepository->createHorarioAtencion($data);
    }

    public function getHorarioAtencionById($id_horario_atencion)
    {
        return $this->horarioAtencionRepository->getHorarioAtencionById($id_horario_atencion);
    }

    public function updateHorarioAtencion($id_horario_atencion, $data)
    {
        return $this->horarioAtencionRepository->updateHorarioAtencion($id_horario_atencion, $data);
    }

    public function deleteHorarioAtencionById($id_horario_atencion)
    {
        return $this->horarioAtencionRepository->deleteHorarioAtencionById($id_horario_atencion);
    }
}