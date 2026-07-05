<?php

namespace App\Http\Controllers;
use App\Services\EstadoPagoService;

class EstadoPagoController extends Controller
{
    protected $estadoPagoService;

    public function __construct(EstadoPagoService $estadoPagoService)
    {
        $this->estadoPagoService = $estadoPagoService;
    }

    public function getAllEstadoPagos()
    {
        $estadoPagos = $this->estadoPagoService->getAllEstadoPagos();
        return response()->json($estadoPagos);
    }

    public function getEstadoPagoById($id)
    {
        $estadoPago = $this->estadoPagoService->getEstadoPagoById($id);
        if ($estadoPago) {
            return response()->json($estadoPago);
        } else {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
    }

    public function createEstadoPago(Request $request)
    {
        $data = $request->all();
        $estadoPago = $this->estadoPagoService->createEstadoPago($data);
        return response()->json($estadoPago, 201);
    }

    public function updateEstadoPago(Request $request, $id)
    {
        $data = $request->all();
        $estadoPago = $this->estadoPagoService->updateEstadoPago($id, $data);
        if ($estadoPago) {
            return response()->json($estadoPago);
        } else {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
    }

    public function deleteEstadoPagoById($id)
    {
        $deleted = $this->estadoPagoService->deleteEstadoPagoById($id);
        if ($deleted) {
            return response()->json(null, 204);
        } else {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
    }
}