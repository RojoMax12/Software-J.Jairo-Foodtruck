<?php

namespace App\Http\Controllers;
use App\Services\HorarioAtencionService;

class HorarioAtencionController extends Controller
{
    protected $horarioAtencionService;

    public function __construct(HorarioAtencionService $horarioAtencionService)
    {
        $this->horarioAtencionService = $horarioAtencionService;
    }

    public function createHorarioAtencion(Request $request)
    {
        $data = $request->all();
        $horarioAtencion = $this->horarioAtencionService->createHorarioAtencion($data);
        return response()->json($horarioAtencion, 201);
    }

    public function getHorarioAtencionesById($id_horario_atencion)
    {
        $horarioAtencion = $this->horarioAtencionService->getHorarioAtencionesById($id_horario_atencion);
        return response()->json($horarioAtencion);
    }

    public function updateHorarioAtencion(Request $request, $id_horario_atencion)
    {
        $data = $request->all();
        $horarioAtencion = $this->horarioAtencionService->updateHorarioAtencion($id_horario_atencion, $data);
        return response()->json($horarioAtencion);
    }

    public function deleteHorarioAtencionById($id_horario_atencion)
    {
        $this->horarioAtencionService->deleteHorarioAtencionById($id_horario_atencion);
        return response()->json(null, 204);
    }
}