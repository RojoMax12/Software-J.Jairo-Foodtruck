<?php

namespace App\Repositories;
use App\Models\Venta;

class VentaRepository
{
    public function getAllVentas()
    {
        return Venta::all();
    }

    public function getVentaById($id)
    {
        return Venta::find($id);
    }

    public function createVenta($data)
    {
        return Venta::create($data);
    }

    public function updateVenta($id, $data)
    {
        $venta = Venta::find($id);
        if ($venta) {
            $venta->update($data);
            return $venta;
        }
        return null;
    }

    public function deleteVenta($id)
    {
        $venta = Venta::find($id);
        if ($venta) {
            $venta->delete();
            return true;
        }
        return false;
    }
}