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
        return response()->json([]);
    }

    public function store(Request $request)
    {
        return $this->createMovimiento($request);
    }

    public function show($id)
    {
        return $this->getMovimientosById($id);
    }

    public function update(Request $request, $id)
    {
        return $this->updateMovimiento($request, $id);
    }

    public function destroy($id)
    {
        return $this->deleteMovimientoById($id);
    }

    public function createMovimiento(Request $request)
    {
        $data = $request->all();
        $movimiento = $this->movimientosService->createMovimiento($data);
        return response()->json($movimiento, 201);
    }

    public function getMovimientosById($id_movimiento)
    {
        $movimiento = $this->movimientosService->getMovimientosById($id_movimiento);
        return response()->json($movimiento);
    }

    public function updateMovimiento(Request $request, $id_movimiento)
    {
        $data = $request->all();
        $movimiento = $this->movimientosService->updateMovimiento($id_movimiento, $data);
        return response()->json($movimiento);
    }

    public function deleteMovimientoById($id_movimiento)
    {
        $this->movimientosService->deleteMovimientoById($id_movimiento);
        return response()->json(null, 204);
    }
}