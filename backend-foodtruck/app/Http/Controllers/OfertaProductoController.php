<?php

namespace App\Http\Controllers;

use App\Services\OfertaProductoService;
use Illuminate\Http\Request;

class OfertaProductoController extends Controller
{
    protected $ofertaProductoService;

    public function __construct(OfertaProductoService $ofertaProductoService)
    {
        $this->ofertaProductoService = $ofertaProductoService;
    }

    public function index()
    {
        return response()->json($this->ofertaProductoService->getAllOfertaProductos());
    }

    public function show($id)
    {
        return response()->json($this->ofertaProductoService->getOfertaProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_oferta' => 'required|integer|exists:ofertas,id_oferta',
            'id_productos' => 'required|integer|exists:productos,id_producto',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'descripcion' => 'nullable|string|max:500',
            'precio_oferta' => 'nullable|integer|min:0',
            'tipo' => 'nullable|string|max:60',
        ]);
        return response()->json($this->ofertaProductoService->createOfertaProducto($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_oferta' => 'sometimes|required|integer|exists:ofertas,id_oferta',
            'id_productos' => 'sometimes|required|integer|exists:productos,id_producto',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'descripcion' => 'nullable|string|max:500',
            'precio_oferta' => 'nullable|integer|min:0',
            'tipo' => 'nullable|string|max:60',
        ]);
        return response()->json($this->ofertaProductoService->updateOfertaProducto($id, $data));
    }

    public function destroy($id)
    {
        $this->ofertaProductoService->deleteOfertaProductoById($id);
        return response()->json(null, 204);
    }
}
