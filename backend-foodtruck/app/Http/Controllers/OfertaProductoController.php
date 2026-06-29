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
        $data = $request->all();
        return response()->json($this->ofertaProductoService->createOfertaProducto($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->ofertaProductoService->updateOfertaProducto($id, $data));
    }

    public function destroy($id)
    {
        $this->ofertaProductoService->deleteOfertaProductoById($id);
        return response()->json(null, 204);
    }
}
