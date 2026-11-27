<?php

namespace App\Http\Controllers;

use App\Services\OfertaService;
use App\Support\CatalogCache;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    protected $ofertaService;

    public function __construct(OfertaService $ofertaService)
    {
        $this->ofertaService = $ofertaService;
    }

    public function index()
    {
        return response()->json($this->ofertaService->getAllOfertas());
    }

    public function show($id)
    {
        return response()->json($this->ofertaService->getOfertaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:500',
            'precio_oferta' => 'required|integer|min:0',
            'fecha' => 'nullable|date',
            'dia_semana' => 'nullable|string|max:30',
        ]);
        $created = $this->ofertaService->createOferta($data);
        CatalogCache::invalidate();
        return response()->json($created, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'descripcion' => 'sometimes|required|string|max:500',
            'precio_oferta' => 'sometimes|required|integer|min:0',
            'fecha' => 'nullable|date',
            'dia_semana' => 'nullable|string|max:30',
        ]);
        $updated = $this->ofertaService->updateOferta($id, $data);
        CatalogCache::invalidate();
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $this->ofertaService->deleteOfertaById($id);
        CatalogCache::invalidate();
        return response()->json(null, 204);
    }
}
