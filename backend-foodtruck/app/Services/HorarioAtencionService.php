<?php

namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Repositories\HorarioAtencionRepository;
use Illuminate\Support\Facades\DB;

class HorarioAtencionService
{
    protected $horarioAtencionRepository;

    public function __construct(HorarioAtencionRepository $horarioAtencionRepository)
    {
        $this->horarioAtencionRepository = $horarioAtencionRepository;
    }

    public function getAllHorarioAtencion()
    {
        return $this->horarioAtencionRepository->getAllHorarioAtencion();
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
        return DB::transaction(function () use ($id_horario_atencion, $data) {
            $horario = $this->horarioAtencionRepository->updateHorarioAtencion($id_horario_atencion, $data);
            if ($horario) {
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $diaNom = $dias[$horario->dia_semana] ?? "Día {$horario->dia_semana}";
                $estado = $horario->activo ? 'Abierto' : 'Cerrado / Día libre';
                $apertura = substr((string)$horario->hora_apertura, 0, 5);
                $cierre = substr((string)$horario->hora_cierre, 0, 5);
                $colchon = (int)($horario->minuto_colchon ?? 30);

                HistorialMovimiento::registrar(
                    'horario',
                    'editar',
                    "Horario de {$diaNom} actualizado",
                    "Día: {$diaNom} ({$estado})",
                    "Horario: {$apertura} a {$cierre} hrs · Colchón: {$colchon} min"
                );
            }
            return $horario;
        });
    }

    public function deleteHorarioAtencionById($id_horario_atencion)
    {
        return $this->horarioAtencionRepository->deleteHorarioAtencionById($id_horario_atencion);
    }
}