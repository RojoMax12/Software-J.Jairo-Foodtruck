<?php

namespace App\Http\Controllers;

use App\Models\Movimientos;
use App\Services\MovimientosServices;
use Illuminate\Http\Request;

class MovimientosController extends Controller
{
    protected $movimientosService;

    public function __construct(MovimientosServices $movimientosService)
    {
        $this->movimientosService = $movimientosService;
    }

    public function index(Request $request)
    {
        $idIngrediente = $request->query('id_ingrediente');
        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = max(1, min(200, (int) $request->query('per_page', 50)));
            $query = Movimientos::with(['ingrediente'])->orderBy('id_movimiento', 'desc');
            if ($idIngrediente) {
                $query->where('id_ingrediente', $idIngrediente);
            }
            return response()->json($query->paginate($perPage));
        }

        $limit = max(1, min(500, (int) $request->query('limit', 100)));
        return response()->json($this->movimientosService->getAllMovimientos($idIngrediente, $limit));
    }

    public function show($id)
    {
        $mov = $this->movimientosService->getMovimientosById($id);
        if (!$mov) {
            return response()->json(['message' => 'Movimiento no encontrado'], 404);
        }
        return response()->json($mov);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede registrar ajustes manuales de kardex.'], 403);
        }

        $data = $request->validate([
            'id_ingrediente' => 'required|integer|exists:ingredientes,id_ingrediente',
            'cantidad' => 'required|numeric|min:0',
            'tipo_movimiento' => 'required|string|max:60',
            'fecha_movimiento' => 'nullable|date',
        ]);
        $movimiento = $this->movimientosService->createMovimiento($data);
        return response()->json($movimiento, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede modificar movimientos de kardex.'], 403);
        }

        $data = $request->validate([
            'id_ingrediente' => 'sometimes|required|integer|exists:ingredientes,id_ingrediente',
            'cantidad' => 'sometimes|required|numeric|min:0',
            'tipo_movimiento' => 'sometimes|required|string|max:60',
            'fecha_movimiento' => 'nullable|date',
        ]);
        $movimiento = $this->movimientosService->updateMovimiento($id, $data);
        if (!$movimiento) {
            return response()->json(['message' => 'Movimiento no encontrado'], 404);
        }
        return response()->json($movimiento);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede eliminar movimientos de kardex.'], 403);
        }

        $deleted = $this->movimientosService->deleteMovimientoById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Movimiento no encontrado'], 404);
        }
        return response()->json(null, 204);
    }

    public function createMovimiento(Request $request)
    {
        return $this->store($request);
    }

    public function getMovimientosById($id)
    {
        return $this->show($id);
    }
}
