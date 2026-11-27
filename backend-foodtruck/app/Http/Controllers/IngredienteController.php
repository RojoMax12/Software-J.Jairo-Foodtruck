<?php

namespace App\Http\Controllers;

use App\Models\Ingrediente;
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

    public function index(Request $request)
    {
        if ($request->boolean('solo_cuantificables')) {
            return response()->json(
                Ingrediente::where('es_cuantificable', true)->orderBy('id_ingrediente', 'asc')->get()
            );
        }
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
            'cantidad_packs' => 'nullable|numeric|min:0',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'fecha_de_ingreso' => 'nullable|date',
            'disponible' => 'nullable|boolean',
            'tipo_inventario' => 'sometimes|string|in:unidad,pack,no_cuantitativo',
            'unidades_por_pack' => 'sometimes|integer|min:1|max:5000',
            'es_cuantificable' => 'sometimes|boolean',
        ]);

        $tipo = $data['tipo_inventario'] ?? (isset($data['es_cuantificable']) && !$data['es_cuantificable'] ? 'no_cuantitativo' : 'unidad');
        $unidadesPorPack = $tipo === 'pack' ? max(1, (int)($data['unidades_por_pack'] ?? 1)) : 1;

        $data['tipo_inventario'] = $tipo;
        $data['unidades_por_pack'] = $unidadesPorPack;
        $data['es_cuantificable'] = $tipo !== 'no_cuantitativo';

        if ($tipo === 'no_cuantitativo') {
            $data['cantidad_actual'] = 0;
            $data['cantidad_minima'] = 0;
        } else {
            if (isset($data['cantidad_packs']) && $tipo === 'pack') {
                $data['cantidad_actual'] = (int)round((float)$data['cantidad_packs'] * $unidadesPorPack);
            } elseif (isset($data['cantidad']) && !isset($data['cantidad_actual'])) {
                $data['cantidad_actual'] = (int)$data['cantidad'];
            } else {
                $data['cantidad_actual'] = (int)($data['cantidad_actual'] ?? 0);
            }
            $data['cantidad_minima'] = (int)($data['cantidad_minima'] ?? 0);
        }

        unset($data['cantidad'], $data['cantidad_packs']);

        if (empty($data['fecha_de_ingreso'])) {
            $data['fecha_de_ingreso'] = now();
        }

        $created = $this->ingredienteService->createIngrediente($data);
        CatalogCache::invalidate();
        return response()->json($created, 201);
    }

    public function update(Request $request, $id)
    {
        $existente = $this->ingredienteService->getIngredienteById($id);
        if (!$existente) {
            return response()->json(['message' => 'Ingrediente no encontrado'], 404);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'descripcion' => 'nullable|string|max:255',
            'cantidad_actual' => 'sometimes|numeric|min:0',
            'cantidad' => 'sometimes|numeric|min:0',
            'cantidad_packs' => 'sometimes|numeric|min:0',
            'cantidad_minima' => 'sometimes|numeric|min:0',
            'fecha_de_ingreso' => 'nullable|date',
            'disponible' => 'sometimes|boolean',
            'tipo_inventario' => 'sometimes|string|in:unidad,pack,no_cuantitativo',
            'unidades_por_pack' => 'sometimes|integer|min:1|max:5000',
            'es_cuantificable' => 'sometimes|boolean',
        ]);

        $tipo = $data['tipo_inventario'] ?? ($existente->tipo_inventario ?? 'unidad');
        if (array_key_exists('es_cuantificable', $data) && !$data['es_cuantificable'] && !isset($data['tipo_inventario'])) {
            $tipo = 'no_cuantitativo';
        }

        $unidadesPorPack = $tipo === 'pack'
            ? max(1, (int)($data['unidades_por_pack'] ?? $existente->unidades_por_pack ?? 1))
            : 1;

        if (isset($data['tipo_inventario']) || isset($data['es_cuantificable']) || isset($data['unidades_por_pack'])) {
            $data['tipo_inventario'] = $tipo;
            $data['unidades_por_pack'] = $unidadesPorPack;
            $data['es_cuantificable'] = $tipo !== 'no_cuantitativo';
        }

        if ($tipo === 'no_cuantitativo') {
            $data['cantidad_actual'] = 0;
            $data['cantidad_minima'] = 0;
        } else {
            if (isset($data['cantidad_packs']) && $tipo === 'pack') {
                $data['cantidad_actual'] = (int)round((float)$data['cantidad_packs'] * $unidadesPorPack);
            } elseif (isset($data['cantidad']) && !isset($data['cantidad_actual'])) {
                $data['cantidad_actual'] = (int)$data['cantidad'];
            }
        }

        unset($data['cantidad'], $data['cantidad_packs']);

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
