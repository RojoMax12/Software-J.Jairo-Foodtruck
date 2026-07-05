<?php

namespace App\Http\Controllers;
use App\Services\ProductoTamañoService;

class ProductoTamañoController extends Controller
{
    protected $productoTamañoService;

    public function __construct(ProductoTamañoService $productoTamañoService)
    {
        $this->productoTamañoService = $productoTamañoService;
    }

    public function createProductoTamaño(Request $request)
    {
        $data = $request->all();
        $productoTamaño = $this->productoTamañoService->createProductoTamaño($data);
        return response()->json($productoTamaño, 201);
    }

    public function getProductoTamañosByProductoId($id_producto)
    {
        $productoTamaños = $this->productoTamañoService->getProductoTamañosByProductoId($id_producto);
        return response()->json($productoTamaños);
    }

    public function updateProductoTamaño(Request $request, $id)
    {
        $data = $request->all();
        $productoTamaño = $this->productoTamañoService->updateProductoTamaño($id, $data);
        return response()->json($productoTamaño);
    }

    public function deleteProductoTamañoById($id)
    {
        $this->productoTamañoService->deleteProductoTamañoById($id);
        return response()->json(null, 204);
    }
}