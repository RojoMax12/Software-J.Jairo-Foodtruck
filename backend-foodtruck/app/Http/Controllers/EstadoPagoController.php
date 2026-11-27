<?php

namespace App\Http\Controllers;

use App\Services\EstadoPagoService;
use Illuminate\Http\Request;

class EstadoPagoController extends Controller
{
    protected $estadoPagoService;

    public function __construct(EstadoPagoService $estadoPagoService)
    {
        $this->estadoPagoService = $estadoPagoService;
    }

    public function index()
    {
        return response()->json($this->estadoPagoService->getAllEstadoPagos());
    }

    public function show($id)
    {
        $estadoPago = $this->estadoPagoService->getEstadoPagoById($id);
        if (!$estadoPago) {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
        return response()->json($estadoPago);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede crear estados de pago.'], 403);
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:80',
        ]);
        $estadoPago = $this->estadoPagoService->createEstadoPago($data);
        return response()->json($estadoPago, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede modificar estados de pago.'], 403);
        }
        if (in_array((int)$id, [1, 2, 3, 4, 5], true)) {
            return response()->json(['message' => 'Los estados base de pago no pueden modificarse.'], 422);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:80',
        ]);
        $estadoPago = $this->estadoPagoService->updateEstadoPago($id, $data);
        if (!$estadoPago) {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
        return response()->json($estadoPago);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede eliminar estados de pago.'], 403);
        }
        if (in_array((int)$id, [1, 2, 3, 4, 5], true)) {
            return response()->json(['message' => 'Los estados base de pago no pueden eliminarse.'], 422);
        }

        $deleted = $this->estadoPagoService->deleteEstadoPagoById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
        return response()->json(null, 204);
    }

    public function getAllEstadoPagos()
    {
        return $this->index();
    }

    public function getEstadoPagoById($id)
    {
        return $this->show($id);
    }

    public function createEstadoPago(Request $request)
    {
        return $this->store($request);
    }
}
