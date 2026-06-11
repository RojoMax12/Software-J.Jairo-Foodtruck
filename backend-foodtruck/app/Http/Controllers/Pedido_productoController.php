<?php

namespace App\Http\Controllers;

use App\Services\Pedido_productoServices;
use Illuminate\Http\Request;

class Pedido_productoController extends Controller
{
    protected $pedidoProductoServices;

    public function __construct(Pedido_productoServices $pedidoProductoServices)
    {
        $this->pedidoProductoServices = $pedidoProductoServices;
    }

    public function index()
    {
        return response()->json($this->pedidoProductoServices->getAllPedidoProductos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoProductoServices->getPedidoProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id',
            'id_producto' => 'required|integer|exists:productos,id',
            'precio_unitario_venta' => 'required|integer|min:0',
            'cantidad' => 'required|integer|min:1',
        ]);

        return response()->json($this->pedidoProductoServices->createPedidoProducto($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_pedido' => 'sometimes|required|integer|exists:pedidos,id',
            'id_producto' => 'sometimes|required|integer|exists:productos,id',
            'precio_unitario_venta' => 'sometimes|required|integer|min:0',
            'cantidad' => 'sometimes|required|integer|min:1',
        ]);

        return response()->json($this->pedidoProductoServices->updatePedidoProducto($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->pedidoProductoServices->deletePedidoProducto($id));
    }
}