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
        $data = $request->all();
        $estadoPago = $this->estadoPagoService->createEstadoPago($data);
        return response()->json($estadoPago, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $estadoPago = $this->estadoPagoService->updateEstadoPago($id, $data);
        if (!$estadoPago) {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
        return response()->json($estadoPago);
    }

    public function destroy($id)
    {
        $deleted = $this->estadoPagoService->deleteEstadoPagoById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Estado de pago no encontrado'], 404);
        }
        return response()->json(null, 204);
    }

    // Aliases para compatibilidad
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