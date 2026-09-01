<?php

namespace App\Http\Controllers;

use App\Services\DetallePedidoService;
use Illuminate\Http\Request;

class DetallePedidoController extends Controller
{
    protected $detallePedidoService;

    public function __construct(DetallePedidoService $detallePedidoService)
    {
        $this->detallePedidoService = $detallePedidoService;
    }

    public function index()
    {
        return response()->json($this->detallePedidoService->getAllDetallePedidos());
    }

    public function show($id)
    {
        $detalle = $this->detallePedidoService->getDetallePedidoById($id);
        if (!$detalle) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }
        return response()->json($detalle);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $detallePedido = $this->detallePedidoService->createDetallePedido($data);
        return response()->json($detallePedido, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $detallePedido = $this->detallePedidoService->updateDetallePedido($id, $data);
        if (!$detallePedido) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }
        return response()->json($detallePedido);
    }

    public function destroy($id)
    {
        $deleted = $this->detallePedidoService->deleteDetallePedidoById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }
        return response()->json(null, 204);
    }

    // Aliases para compatibilidad
    public function createDetallePedido(Request $request)
    {
        return $this->store($request);
    }

    public function getDetallePedidosByPedidoId($id_pedido)
    {
        $detallePedidos = $this->detallePedidoService->getDetallePedidosByPedidoId($id_pedido);
        return response()->json($detallePedidos);
    }
}