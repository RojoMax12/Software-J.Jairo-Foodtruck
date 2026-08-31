<?php

namespace App\Repositories;

use App\Models\Detalle_pedido_Ingrediente;

class DetallePedidoIngredienteRepository
{
    public function getAllDetallePedidoIngredientes()
    {
        return Detalle_pedido_Ingrediente::with(['ingrediente'])->get();
    }

    public function getDetallePedidoIngredienteById($id)
    {
        return Detalle_pedido_Ingrediente::with(['ingrediente'])->find($id);
    }

    public function createDetallePedidoIngrediente($data)
    {
        return Detalle_pedido_Ingrediente::create($data);
    }

    public function getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido)
    {
        return Detalle_pedido_Ingrediente::with(['ingrediente'])->where('id_detalle_pedido', $id_detalle_pedido)->get();
    }

    public function updateDetallePedidoIngrediente($id, $data)
    {
        $detallePedidoIngrediente = Detalle_pedido_Ingrediente::find($id);
        if ($detallePedidoIngrediente) {
            $detallePedidoIngrediente->update($data);
            return $detallePedidoIngrediente;
        }
        return null;
    }

    public function deleteDetallePedidoIngredienteById($id)
    {
        $detallePedidoIngrediente = Detalle_pedido_Ingrediente::find($id);
        if ($detallePedidoIngrediente) {
            $detallePedidoIngrediente->delete();
            return true;
        }
        return false;
    }
}