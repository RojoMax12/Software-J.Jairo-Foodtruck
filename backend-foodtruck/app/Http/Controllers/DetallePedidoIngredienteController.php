<?php

namespace App\Http\Controllers;
use App\Services\DetallePedidoIngredienteService;

class DetallePedidoIngredienteController extends Controller
{
    protected $detallePedidoIngredienteService;

    public function __construct(DetallePedidoIngredienteService $detallePedidoIngredienteService)
    {
        $this->detallePedidoIngredienteService = $detallePedidoIngredienteService;
    }

    public function createDetallePedidoIngrediente(Request $request)
    {
        $data = $request->all();
        $detallePedidoIngrediente = $this->detallePedidoIngredienteService->createDetallePedidoIngrediente($data);
        return response()->json($detallePedidoIngrediente, 201);
    }

    public function getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido)
    {
        $detallePedidoIngredientes = $this->detallePedidoIngredienteService->getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido);
        return response()->json($detallePedidoIngredientes);
    }

    public function updateDetallePedidoIngrediente(Request $request, $id)
    {
        $data = $request->all();
        $detallePedidoIngrediente = $this->detallePedidoIngredienteService->updateDetallePedidoIngrediente($id, $data);
        return response()->json($detallePedidoIngrediente);
    }

    public function deleteDetallePedidoIngredienteById($id)
    {
        $this->detallePedidoIngredienteService->deleteDetallePedidoIngredienteById($id);
        return response()->json(null, 204);
    }
}