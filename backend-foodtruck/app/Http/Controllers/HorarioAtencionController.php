<?php

namespace App\Http\Controllers;

use App\Services\HorarioAtencionService;
use Illuminate\Http\Request;

class HorarioAtencionController extends Controller
{
    protected $horarioAtencionService;

    public function __construct(HorarioAtencionService $horarioAtencionService)
    {
        $this->horarioAtencionService = $horarioAtencionService;
    }

    public function index()
    {
        return response()->json($this->horarioAtencionService->getAllHorarioAtencion());
    }

    public function show($id)
    {
        $horario = $this->horarioAtencionService->getHorarioAtencionById($id);
        if (!$horario) {
            return response()->json(['message' => 'Horario de atención no encontrado'], 404);
        }
        return response()->json($horario);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $horarioAtencion = $this->horarioAtencionService->createHorarioAtencion($data);
        return response()->json($horarioAtencion, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'hora_apertura' => ['sometimes', 'required', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/'],
            'hora_cierre' => ['sometimes', 'required', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/'],
            'minuto_colchon' => 'sometimes|nullable|integer|min:0|max:180',
            'activo' => 'sometimes|boolean',
        ]);

        if ($request->user()) {
            $validated['id_usuario'] = $request->user()->id_usuario ?? $request->user()->id;
        }

        $horarioAtencion = $this->horarioAtencionService->updateHorarioAtencion($id, $validated);
        if (!$horarioAtencion) {
            return response()->json(['message' => 'Horario de atención no encontrado'], 404);
        }
        return response()->json($horarioAtencion);
    }

    public function destroy($id)
    {
        $deleted = $this->horarioAtencionService->deleteHorarioAtencionById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Horario de atención no encontrado'], 404);
        }
        return response()->json(null, 204);
    }

    // Aliases para compatibilidad
    public function createHorarioAtencion(Request $request)
    {
        return $this->store($request);
    }

    public function getHorarioAtencionesById($id)
    {
        return $this->show($id);
    }

    public function updateHorarioAtencion(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    public function deleteHorarioAtencionById($id)
    {
        return $this->destroy($id);
    }

    public function getTurnoActual(Request $request)
    {
        $targetDate = $request->query('fecha') ?? $request->query('date');
        $pedidoRepo = app(\App\Repositories\PedidoRepository::class);
        $window = $pedidoRepo->getShiftWindow($targetDate);

        $shiftDate = $window['shift_date'] ?? $window['start']->format('Y-m-d');

        return response()->json([
            'start' => $window['start']->format('Y-m-d H:i:s'),
            'end' => $window['end']->format('Y-m-d H:i:s'),
            'start_timestamp' => $window['start']->getTimestamp() * 1000,
            'end_timestamp' => $window['end']->getTimestamp() * 1000,
            'hora_apertura' => substr($window['hora_apertura'] ?? '19:00', 0, 5),
            'hora_cierre' => substr($window['hora_cierre'] ?? '00:30', 0, 5),
            'dia' => $window['dia'] ?? 'Hoy',
            'es_jornada_activa' => (bool)($window['is_active'] ?? false),
            'es_dia_cerrado' => (bool)($window['is_day_off'] ?? false),
            'shift_date' => $shiftDate,
        ]);
    }
}