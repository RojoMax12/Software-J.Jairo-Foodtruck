<?php

namespace App\Http\Controllers;

use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    protected $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    public function index()
    {
        return response()->json($this->categoriaService->getAllCategorias());
    }

    public function show($id)
    {
        return response()->json($this->categoriaService->getCategoriaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->categoriaService->createCategoria($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->categoriaService->updateCategoria($id, $data));
    }

    public function destroy($id)
    {
        $this->categoriaService->deleteCategoriaById($id);
        return response()->json(null, 204);
    }
}
