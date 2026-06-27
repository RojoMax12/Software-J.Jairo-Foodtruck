<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    protected $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    public function index()
    {
        return response()->json($this->productoService->getAllProductos());
    }

    public function show($id)
    {
        return response()->json($this->productoService->getProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->productoService->createProducto($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->productoService->updateProducto($id, $data));
    }

    public function destroy($id)
    {
        $this->productoService->deleteProductoById($id);
        return response()->json(null, 204);
    }
}
