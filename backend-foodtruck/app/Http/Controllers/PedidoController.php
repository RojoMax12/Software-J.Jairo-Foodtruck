<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function index()
    {
        return response()->json($this->pedidoService->getAllPedidos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoService->getPedidoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->pedidoService->createPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->pedidoService->updatePedido($id, $data));
    }

    public function destroy($id)
    {
        $this->pedidoService->deletePedidoById($id);
        return response()->json(null, 204);
    }
}
