<?php
namespace App\Repositories;
use App\Models\Estado_pedido;

# Repositorio EstadoPedido
class EstadoPedidoRepository
{
    # Create
    public function createEstadoPedido($data)
    {
        return Estado_pedido::create($data);
    }

    # Geters
    public function getAllEstadosPedido()
    {
        return Estado_pedido::all();
    }

    public function getEstadoPedidoById($id)
    {
        return Estado_pedido::find($id);
    }

    public function getEstadoPedidoByNombre($nombre)
    {
        return Estado_pedido::where('nombre', $nombre)->first();
    }

    public function getPedidosByEstadoId($id)
    {
        $estado = Estado_pedido::find($id);
        if ($estado) {
            return $estado->pedidos;
        }
        return null;
    }

    # Seters
    public function updateEstadoPedido($id, $data)
    {
        $estado = Estado_pedido::find($id);
        if ($estado) {
            $estado->update($data);
            return $estado;
        }
        return null;
    }

    # Delete
    public function deleteEstadoPedidoById($id)
    {
        $estado = Estado_pedido::find($id);
        if ($estado) {
            $estado->delete();
            return true;
        }
        return false;
    }
}