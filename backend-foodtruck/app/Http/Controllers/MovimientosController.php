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

    public function index()
    {
        return response()->json($this->movimientosService->getAllMovimientos());
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
        $data = $request->all();
        $movimiento = $this->movimientosService->createMovimiento($data);
        return response()->json($movimiento, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
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

    // Aliases para compatibilidad
    public function createMovimiento(Request $request)
    {
        return $this->store($request);
    }

    public function getMovimientosById($id)
    {
        return $this->show($id);
    }
}