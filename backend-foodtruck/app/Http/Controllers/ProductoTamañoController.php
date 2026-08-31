<?php

namespace App\Http\Controllers;

use App\Services\ProductoTamañoService;
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
        $data = $request->all();
        $productoTamaño = $this->productoTamañoService->createProductoTamaño($data);
        return response()->json($productoTamaño, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $productoTamaño = $this->productoTamañoService->updateProductoTamaño($id, $data);
        if (!$productoTamaño) {
            return response()->json(['message' => 'Relación Producto-Tamaño no encontrada'], 404);
        }
        return response()->json($productoTamaño);
    }

    public function destroy($id)
    {
        $deleted = $this->productoTamañoService->deleteProductoTamañoById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Relación Producto-Tamaño no encontrada'], 404);
        }
        return response()->json(null, 204);
    }

    // Aliases para compatibilidad
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