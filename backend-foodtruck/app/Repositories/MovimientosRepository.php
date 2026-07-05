<?php

namespace App\Repositories;
use App\Models\Movimientos;

class MovimientosRepository
{
    public function createMovimiento($data)
    {
        return Movimientos::create($data);
    }

    public function getMovimientos()
    {
        return Movimientos::all();
    }

    public function updateMovimiento($id, $data)
    {
        $movimiento = Movimientos::find($id);
        if ($movimiento) {
            $movimiento->update($data);
            return $movimiento;
        }
        return null;
    }

    public function deleteMovimientoById($id)
    {
        $movimiento = Movimientos::find($id);
        if ($movimiento) {
            $movimiento->delete();
            return true;
        }
        return false;
    }
}