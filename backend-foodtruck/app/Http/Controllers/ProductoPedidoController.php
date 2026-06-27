<?php

namespace App\Http\Controllers;

use App\Services\ProductoPedidoService;
use Illuminate\Http\Request;

class ProductoPedidoController extends Controller
{
    protected $productoPedidoService;

    public function __construct(ProductoPedidoService $productoPedidoService)
    {
        $this->productoPedidoService = $productoPedidoService;
    }

    public function index()
    {
        return response()->json($this->productoPedidoService->getAllProductoPedidos());
    }

    public function show($id)
    {
        return response()->json($this->productoPedidoService->getProductoPedidoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->productoPedidoService->createProductoPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->productoPedidoService->updateProductoPedido($id, $data));
    }

    public function destroy($id)
    {
        $this->productoPedidoService->deleteProductoPedidoById($id);
        return response()->json(null, 204);
    }
}
