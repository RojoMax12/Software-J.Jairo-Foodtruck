<?php
namespace App\Repositories;
use App\Models\Caja;

# Repositorio Caja
class CajaRepository
{
    # Create
    public function createCaja($data)
    {
        return Caja::create($data);
    }

    # Geters
    public function getAllCajas()
    {
        return Caja::all();
    }

    public function getCajaById($id)
    {
        return Caja::find($id);
    }

    public function getCajasByUsuarioId($idUsuario)
    {
        return Caja::where('id_usuario', $idUsuario)->get();
    }

    public function getCajaByVentaId($idVenta)
    {
        return Caja::where('id_venta', $idVenta)->first();
    }

    # Seters
    public function updateCaja($id, $data)
    {
        $caja = Caja::find($id);
        if ($caja) {
            $caja->update($data);
            return $caja;
        }
        return null;
    }

    # Delete
    public function deleteCajaById($id)
    {
        $caja = Caja::find($id);
        if ($caja) {
            $caja->delete();
            return true;
        }
        return false;
    }
}