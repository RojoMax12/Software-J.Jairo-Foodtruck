<?php

namespace App\Http\Controllers;

use App\Services\EstadoPedidoService;
use Illuminate\Http\Request;

class EstadoPedidoController extends Controller
{
    protected $estadoPedidoService;

    public function __construct(EstadoPedidoService $estadoPedidoService)
    {
        $this->estadoPedidoService = $estadoPedidoService;
    }

    public function index()
    {
        return response()->json($this->estadoPedidoService->getAllEstadoPedidos());
    }

    public function show($id)
    {
        return response()->json($this->estadoPedidoService->getEstadoPedidoById($id));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede crear estados de pedido.'], 403);
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:80',
        ]);
        return response()->json($this->estadoPedidoService->createEstadoPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede modificar estados de pedido.'], 403);
        }
        if (in_array((int)$id, [1, 2, 3, 4, 5], true)) {
            return response()->json(['message' => 'Los estados base de pedido no pueden modificarse.'], 422);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:80',
        ]);
        return response()->json($this->estadoPedidoService->updateEstadoPedido($id, $data));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede eliminar estados de pedido.'], 403);
        }
        if (in_array((int)$id, [1, 2, 3, 4, 5], true)) {
            return response()->json(['message' => 'Los estados base de pedido no pueden eliminarse.'], 422);
        }

        $this->estadoPedidoService->deleteEstadoPedidoById($id);
        return response()->json(null, 204);
    }
}
