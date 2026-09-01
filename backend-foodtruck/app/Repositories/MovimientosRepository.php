<?php

namespace App\Repositories;

use App\Models\Movimientos;

class MovimientosRepository
{
    public function getAllMovimientos()
    {
        return Movimientos::with(['ingrediente'])->orderBy('id_movimiento', 'desc')->get();
    }

    public function getMovimientos()
    {
        return $this->getAllMovimientos();
    }

    public function createMovimiento($data)
    {
        return Movimientos::create($data);
    }

    public function getMovimientosById($id)
    {
        return Movimientos::with(['ingrediente'])->find($id);
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