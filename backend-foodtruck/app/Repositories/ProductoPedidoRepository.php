<?php
namespace App\Repositories;
use App\Models\Producto_pedido;

# Repositorio ProductoPedido
class ProductoPedidoRepository
{
    # Create
    public function createProductoPedido($data)
    {
        return Producto_pedido::create($data);
    }

    # Geters
    public function getAllProductoPedidos()
    {
        return Producto_pedido::all();
    }

    public function getProductoPedidoById($id)
    {
        return Producto_pedido::find($id);
    }

    public function getProductoPedidosByProductoId($idProducto)
    {
        return Producto_pedido::where('id_producto', $idProducto)->get();
    }

    public function getProductoPedidosByPedidoId($idPedido)
    {
        return Producto_pedido::where('id_pedido', $idPedido)->get();
    }

    # Seters
    public function updateProductoPedido($id, $data)
    {
        $productoPedido = Producto_pedido::find($id);
        if ($productoPedido) {
            $productoPedido->update($data);
            return $productoPedido;
        }
        return null;
    }
    
    # Delete
    public function deleteProductoPedidoById($id)
    {
        $productoPedido = Producto_pedido::find($id);
        if ($productoPedido) {
            $productoPedido->delete();
            return true;
        }
        return false;
    }
}