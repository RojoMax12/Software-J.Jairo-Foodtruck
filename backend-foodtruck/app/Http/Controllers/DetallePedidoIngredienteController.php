<?php

namespace App\Http\Controllers;

use App\Services\DetallePedidoIngredienteService;
use Illuminate\Http\Request;

class DetallePedidoIngredienteController extends Controller
{
    protected $detallePedidoIngredienteService;

    public function __construct(DetallePedidoIngredienteService $detallePedidoIngredienteService)
    {
        $this->detallePedidoIngredienteService = $detallePedidoIngredienteService;
    }

    public function index()
    {
        return response()->json($this->detallePedidoIngredienteService->getAllDetallePedidoIngredientes());
    }

    public function show($id)
    {
        $detalleIng = $this->detallePedidoIngredienteService->getDetallePedidoIngredienteById($id);
        if (!$detalleIng) {
            return response()->json(['message' => 'Modificación de ingrediente no encontrada'], 404);
        }
        return response()->json($detalleIng);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $detallePedidoIngrediente = $this->detallePedidoIngredienteService->createDetallePedidoIngrediente($data);
        return response()->json($detallePedidoIngrediente, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $detallePedidoIngrediente = $this->detallePedidoIngredienteService->updateDetallePedidoIngrediente($id, $data);
        if (!$detallePedidoIngrediente) {
            return response()->json(['message' => 'Modificación de ingrediente no encontrada'], 404);
        }
        return response()->json($detallePedidoIngrediente);
    }

    public function destroy($id)
    {
        $deleted = $this->detallePedidoIngredienteService->deleteDetallePedidoIngredienteById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Modificación de ingrediente no encontrada'], 404);
        }
        return response()->json(null, 204);
    }

    // Aliases para compatibilidad
    public function createDetallePedidoIngrediente(Request $request)
    {
        return $this->store($request);
    }

    public function getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido)
    {
        $detallePedidoIngredientes = $this->detallePedidoIngredienteService->getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido);
        return response()->json($detallePedidoIngredientes);
    }
}