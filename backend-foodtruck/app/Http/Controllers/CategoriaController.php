<?php

namespace App\Http\Controllers;

use App\Services\CategoriaService;
use App\Support\CatalogCache;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    protected $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    public function index(Request $request)
    {
        $data = CatalogCache::rememberCategories(fn () => $this->categoriaService->getAllCategorias());
        return CatalogCache::respondWithEtag($request, $data);
    }

    public function show($id)
    {
        return response()->json($this->categoriaService->getCategoriaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_categoria' => 'required|string|max:120',
            'descripcion_categoria' => 'nullable|string|max:500',
        ]);
        $created = $this->categoriaService->createCategoria($data);
        CatalogCache::invalidate();
        return response()->json($created, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_categoria' => 'sometimes|required|string|max:120',
            'descripcion_categoria' => 'nullable|string|max:500',
        ]);
        $updated = $this->categoriaService->updateCategoria($id, $data);
        CatalogCache::invalidate();
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $this->categoriaService->deleteCategoriaById($id);
        CatalogCache::invalidate();
        return response()->json(null, 204);
    }
}
