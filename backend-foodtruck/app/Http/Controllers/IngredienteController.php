<?php

namespace App\Http\Controllers;

use App\Services\IngredienteService;
use App\Support\CatalogCache;
use Illuminate\Http\Request;

class IngredienteController extends Controller
{
    protected $ingredienteService;

    public function __construct(IngredienteService $ingredienteService)
    {
        $this->ingredienteService = $ingredienteService;
    }

    public function index()
    {
        return response()->json($this->ingredienteService->getAllIngredientes());
    }

    public function show($id)
    {
        return response()->json($this->ingredienteService->getIngredienteById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:255',
            'cantidad_actual' => 'nullable|numeric|min:0',
            'cantidad' => 'nullable|numeric|min:0',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'fecha_de_ingreso' => 'nullable|date',
            'disponible' => 'nullable|boolean',
        ]);

        if (isset($data['cantidad']) && !isset($data['cantidad_actual'])) {
            $data['cantidad_actual'] = $data['cantidad'];
        }

        if (empty($data['fecha_de_ingreso'])) {
            $data['fecha_de_ingreso'] = now();
        }

        $created = $this->ingredienteService->createIngrediente($data);
        CatalogCache::invalidate();
        return response()->json($created, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'descripcion' => 'nullable|string|max:255',
            'cantidad_actual' => 'sometimes|numeric|min:0',
            'cantidad' => 'sometimes|numeric|min:0',
            'cantidad_minima' => 'sometimes|numeric|min:0',
            'fecha_de_ingreso' => 'nullable|date',
            'disponible' => 'sometimes|boolean',
        ]);

        if (isset($data['cantidad']) && !isset($data['cantidad_actual'])) {
            $data['cantidad_actual'] = $data['cantidad'];
        }

        $updated = $this->ingredienteService->updateIngrediente($id, $data);
        CatalogCache::invalidate();
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $this->ingredienteService->deleteIngredienteById($id);
        CatalogCache::invalidate();
        return response()->json(null, 204);
    }
}
