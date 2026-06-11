<?php

namespace App\Repositories;
use App\Models\Pedido;

class PedidoRepository {
    public function getAllPedidos()
    {
        return Pedido::all();
    }

    public function getPedidoById($id)
    {
        return Pedido::find($id);
    }
    public function createPedido($data)
    {
        return Pedido::create($data);
    }

    public function updatePedido($id, $data)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->update($data);
            return $pedido;
        }
        return null;
    }

    public function deletePedido($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->delete();
            return true;
        }
        return false;
    }

     public function getPedidoByUsuario($id)
    { 
        return Pedido::where('id_usuario_dicreme', $id)->get();
    }

    public function getPedidoByUsuario_distribuidores($id){

        return Pedido::where('id_usuario_distribuidor', $id)->get();
    }

}