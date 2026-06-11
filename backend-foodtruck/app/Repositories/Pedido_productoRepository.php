<?php

namespace App\Repositories;
use App\Models\Pedido_producto;

# Repositorio Pedido_producto
class Pedido_productoRepository
{
    # Create
    public function createPedidoProducto($data)
    {
        return Pedido_producto::create($data);
    }

    # Geters
    public function getAllPedidoProductos()
    {
        return Pedido_producto::all();
    }

    public function getPedidoProductoById($id)
    {
        return Pedido_producto::find($id);
    }

    public function getPedidoProductosByPedidoId($idPedido)
    {
        return Pedido_producto::where('id_pedido', $idPedido)->get();
    }

    public function getPedidoProductosByProductoId($idProducto)
    {
        return Pedido_producto::where('id_producto', $idProducto)->get();
    }

    # Seters
    public function updatePedidoProducto($id, $data)
    {
        $pedidoProducto = Pedido_producto::find($id);
        if ($pedidoProducto) {
            $pedidoProducto->update($data);
            return $pedidoProducto;
        }
        return null;
    }

    # Delete
    public function deletePedidoProducto($id)
    {
        $pedidoProducto = Pedido_producto::find($id);
        if ($pedidoProducto) {
            $pedidoProducto->delete();
            return true;
        }
        return false;
    }
}