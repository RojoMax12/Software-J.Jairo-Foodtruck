<?php

namespace App\Repositories;
use App\Models\Estado_pedido;

class Estado_pedidoRepository  {

    public function getAllEstadosPedido(){
        return Estado_pedido::all();
    }

    public function getEstadoPedidoById($id){
        return Estado_pedido::find($id);
    }
    public function createEstadoPedido($data){
        return Estado_pedido::create($data);
    }

    public function updateEstadoPedido($id, $data){
        $estadoPedido = Estado_pedido::find($id);
        if ($estadoPedido) {
            $estadoPedido->update($data);
            return $estadoPedido;
        }
        return null;
    }

    public function deleteEstadoPedido($id){
        $estadoPedido = Estado_pedido::find($id);
        if ($estadoPedido) {
            $estadoPedido->delete();
            return true;
        }
        return false;
    }





}