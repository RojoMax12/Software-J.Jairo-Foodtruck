<?php

namespace App\Http\Controllers;

use App\Services\ProductoIngredienteService;
use Illuminate\Http\Request;

class ProductoIngredienteController extends Controller
{
    protected $productoIngredienteService;

    public function __construct(ProductoIngredienteService $productoIngredienteService)
    {
        $this->productoIngredienteService = $productoIngredienteService;
    }

    public function index()
    {
        return response()->json($this->productoIngredienteService->getAllProductoIngredientes());
    }

    public function show($id)
    {
        return response()->json($this->productoIngredienteService->getProductoIngredienteById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->productoIngredienteService->createProductoIngrediente($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->productoIngredienteService->updateProductoIngrediente($id, $data));
    }

    public function destroy($id)
    {
        $this->productoIngredienteService->deleteProductoIngredienteById($id);
        return response()->json(null, 204);
    }
}
