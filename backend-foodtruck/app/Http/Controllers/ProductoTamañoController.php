<?php

namespace App\Http\Controllers;

use App\Services\ProductoTamañoService;
use App\Support\CatalogCache;
use Illuminate\Http\Request;

class ProductoTamañoController extends Controller
{
    protected $productoTamañoService;

    public function __construct(ProductoTamañoService $productoTamañoService)
    {
        $this->productoTamañoService = $productoTamañoService;
    }

    public function index()
    {
        return response()->json($this->productoTamañoService->getAllProductoTamaños());
    }

    public function show($id)
    {
        $item = $this->productoTamañoService->getProductoTamañoById($id);
        if (!$item) {
            return response()->json(['message' => 'Relación Producto-Tamaño no encontrada'], 404);
        }
        return response()->json($item);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'id_tamaño' => 'required|integer|exists:tamaños,id_tamaño',
            'precio' => 'required|integer|min:0',
        ]);
        $productoTamaño = $this->productoTamañoService->createProductoTamaño($data);
        CatalogCache::invalidate();
        return response()->json($productoTamaño, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_producto' => 'sometimes|required|integer|exists:productos,id_producto',
            'id_tamaño' => 'sometimes|required|integer|exists:tamaños,id_tamaño',
            'precio' => 'sometimes|required|integer|min:0',
        ]);
        $productoTamaño = $this->productoTamañoService->updateProductoTamaño($id, $data);
        if (!$productoTamaño) {
            return response()->json(['message' => 'Relación Producto-Tamaño no encontrada'], 404);
        }
        CatalogCache::invalidate();
        return response()->json($productoTamaño);
    }

    public function destroy($id)
    {
        $deleted = $this->productoTamañoService->deleteProductoTamañoById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Relación Producto-Tamaño no encontrada'], 404);
        }
        CatalogCache::invalidate();
        return response()->json(null, 204);
    }

    public function createProductoTamaño(Request $request)
    {
        return $this->store($request);
    }

    public function getProductoTamañosByProductoId($id_producto)
    {
        $productoTamaños = $this->productoTamañoService->getProductoTamañosByProductoId($id_producto);
        return response()->json($productoTamaños);
    }
}
