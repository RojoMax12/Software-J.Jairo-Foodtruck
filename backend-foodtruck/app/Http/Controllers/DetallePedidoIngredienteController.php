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
        $data = $request->validate([
            'id_detalle_pedido' => 'required|integer|exists:detalle_pedido,id_detalle_pedido',
            'id_ingrediente' => 'required|integer|exists:ingredientes,id_ingrediente',
            'tipo_modificacion' => 'required|string|max:40',
            'precio_aplicado' => 'nullable|numeric|min:0',
        ]);
        $detallePedidoIngrediente = $this->detallePedidoIngredienteService->createDetallePedidoIngrediente($data);
        return response()->json($detallePedidoIngrediente, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'tipo_modificacion' => 'sometimes|string|max:40',
            'precio_aplicado' => 'nullable|numeric|min:0',
        ]);
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
