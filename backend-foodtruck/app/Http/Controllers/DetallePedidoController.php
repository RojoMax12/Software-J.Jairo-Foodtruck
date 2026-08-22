<?php

namespace App\Http\Controllers;
use App\Services\DetallePedidoService;

class DetallePedidoController extends Controller
{
    protected $detallePedidoService;

    public function __construct(DetallePedidoService $detallePedidoService)
    {
        $this->detallePedidoService = $detallePedidoService;
    }

    public function createDetallePedido(Request $request)
    {
        $data = $request->all();
        $detallePedido = $this->detallePedidoService->createDetallePedido($data);
        return response()->json($detallePedido, 201);
    }

    public function getDetallePedidosByPedidoId($id_pedido)
    {
        $detallePedidos = $this->detallePedidoService->getDetallePedidosByPedidoId($id_pedido);
        return response()->json($detallePedidos);
    }

    public function updateDetallePedido(Request $request, $id)
    {
        $data = $request->all();
        $detallePedido = $this->detallePedidoService->updateDetallePedido($id, $data);
        return response()->json($detallePedido);
    }

    public function deleteDetallePedidoById($id)
    {
        $this->detallePedidoService->deleteDetallePedidoById($id);
        return response()->json(null, 204);
    }
}