<?php
namespace App\Repositories;
use App\Models\Pedido;

# Repositorio Pedido
class PedidoRepository
{
    # Create
    public function createPedido($data)
    {
        return Pedido::create($data);
    }

    # Geters
    public function getAllPedidos()
    {
        return Pedido::all();
    }

    public function getPedidoById($id)
    {
        return Pedido::find($id);
    }

    public function getPedidosByUsuarioId($idUsuario)
    {
        return Pedido::where('id_usuario', $idUsuario)->get();
    }

    public function getPedidosByEstadoId($idEstado)
    {
        return Pedido::where('id_estado_pedido', $idEstado)->get();
    }

    public function getPedidosByEstadoPago($estadoPago)
    {
        return Pedido::where('estado_pago', $estadoPago)->get();
    }

    public function getVentaByPedidoId($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            return $pedido->venta;
        }
        return null;
    }

    # Seters
    public function updatePedido($id, $data)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->update($data);
            return $pedido;
        }
        return null;
    }

    # Delete
    public function deletePedidoById($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->delete();
            return true;
        }
        return false;
    }
}