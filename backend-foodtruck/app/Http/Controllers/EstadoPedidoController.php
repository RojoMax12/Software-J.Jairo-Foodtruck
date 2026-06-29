<?php

namespace App\Http\Controllers;

use App\Services\EstadoPedidoService;
use Illuminate\Http\Request;

class EstadoPedidoController extends Controller
{
    protected $estadoPedidoService;

    public function __construct(EstadoPedidoService $estadoPedidoService)
    {
        $this->estadoPedidoService = $estadoPedidoService;
    }

    public function index()
    {
        return response()->json($this->estadoPedidoService->getAllEstadoPedidos());
    }

    public function show($id)
    {
        return response()->json($this->estadoPedidoService->getEstadoPedidoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->estadoPedidoService->createEstadoPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->estadoPedidoService->updateEstadoPedido($id, $data));
    }

    public function destroy($id)
    {
        $this->estadoPedidoService->deleteEstadoPedidoById($id);
        return response()->json(null, 204);
    }
}
