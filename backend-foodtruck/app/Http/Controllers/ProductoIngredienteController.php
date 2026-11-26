<?php

namespace App\Http\Controllers;

use App\Services\ProductoIngredienteService;
use App\Support\CatalogCache;
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
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'id_ingrediente' => 'required|integer|exists:ingredientes,id_ingrediente',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'cantidad' => 'nullable|integer|min:0|max:100',
            'incluido_por_defecto' => 'sometimes|boolean',
            'precio_extra' => 'nullable|integer|min:0',
        ]);
        $res = $this->productoIngredienteService->createProductoIngrediente($data);
        CatalogCache::invalidate();
        return response()->json($res, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_producto' => 'sometimes|required|integer|exists:productos,id_producto',
            'id_ingrediente' => 'sometimes|required|integer|exists:ingredientes,id_ingrediente',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'cantidad' => 'nullable|integer|min:0|max:100',
            'incluido_por_defecto' => 'sometimes|boolean',
            'precio_extra' => 'nullable|integer|min:0',
        ]);
        $res = $this->productoIngredienteService->updateProductoIngrediente($id, $data);
        CatalogCache::invalidate();
        return response()->json($res);
    }

    public function destroy($id)
    {
        $this->productoIngredienteService->deleteProductoIngredienteById($id);
        CatalogCache::invalidate();
        return response()->json(null, 204);
    }
}
