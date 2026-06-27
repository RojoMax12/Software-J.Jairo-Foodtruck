<?php
namespace App\Repositories;
use App\Models\EstadoPedido;

# Repositorio EstadoPedido
class EstadoPedidoRepository
{
    # Create
    public function createEstadoPedido($data)
    {
        return EstadoPedido::create($data);
    }

    # Geters
    public function getAllEstadosPedido()
    {
        return EstadoPedido::all();
    }

    public function getEstadoPedidoById($id)
    {
        return EstadoPedido::find($id);
    }

    public function getEstadoPedidoByNombre($nombre)
    {
        return EstadoPedido::where('nombre', $nombre)->first();
    }

    public function getPedidosByEstadoId($id)
    {
        $estado = EstadoPedido::find($id);
        if ($estado) {
            return $estado->pedidos;
        }
        return null;
    }

    # Seters
    public function updateEstadoPedido($id, $data)
    {
        $estado = EstadoPedido::find($id);
        if ($estado) {
            $estado->update($data);
            return $estado;
        }
        return null;
    }

    # Delete
    public function deleteEstadoPedidoById($id)
    {
        $estado = EstadoPedido::find($id);
        if ($estado) {
            $estado->delete();
            return true;
        }
        return false;
    }
}