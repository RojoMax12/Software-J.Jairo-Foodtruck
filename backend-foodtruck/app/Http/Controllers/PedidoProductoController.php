<?php

namespace App\Http\Controllers;

use App\Services\PedidoProductoService;
use Illuminate\Http\Request;

class PedidoProductoController extends Controller
{
    protected $pedidoProductoService;

    public function __construct(PedidoProductoService $pedidoProductoService)
    {
        $this->pedidoProductoService = $pedidoProductoService;
    }

    public function index()
    {
        return response()->json($this->pedidoProductoService->getAllPedidoProductos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoProductoService->getPedidoProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->pedidoProductoService->createPedidoProducto($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->pedidoProductoService->updatePedidoProducto($id, $data));
    }

    public function destroy($id)
    {
        $this->pedidoProductoService->deletePedidoProductoById($id);
        return response()->json(null, 204);
    }
}
