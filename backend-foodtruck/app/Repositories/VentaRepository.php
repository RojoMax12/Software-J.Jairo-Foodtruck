<?php
namespace App\Repositories;
use App\Models\Venta;
# Repositorio Venta
class VentaRepository
{
    # Create
    public function createVenta($data)
    {
        return Venta::create($data);
    }
    
    # Geters
    public function getAllVentas()
    {
        return Venta::with(['pedido', 'caja'])->orderBy('id_venta', 'desc')->get();
    }

    public function getVentaById($id)
    {
        return Venta::with(['pedido', 'caja'])->find($id);
    }

    public function getVentaByPedidoId($idPedido)
    {
        return Venta::where('id_pedido', $idPedido)->first();
    }

    public function getCajasByVentaId($id)
    {
        $venta = Venta::find($id);
        if ($venta) {
            return $venta->cajas;
        }
        return null;
    }

    # Seters
    public function updateVenta($id, $data)
    {
        $venta = Venta::find($id);
        if ($venta) {
            $venta->update($data);
            return $venta;
        }
        return null;
    }

    # Delete
    public function deleteVentaById($id)
    {
        $venta = Venta::find($id);
        if ($venta) {
            $venta->delete();
            return true;
        }
        return false;
    }
}