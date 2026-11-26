<?php

namespace App\Http\Controllers;

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
        $limit = (int) $request->query('limit', 100);
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

    public function destroy($id)
    {
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
