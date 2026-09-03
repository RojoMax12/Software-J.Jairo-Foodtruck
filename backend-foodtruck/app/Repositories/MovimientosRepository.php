<?php

namespace App\Repositories;

use App\Models\Movimientos;

class MovimientosRepository
{
    public function getAllMovimientos($idIngrediente = null, $limit = 100)
    {
        $query = Movimientos::with(['ingrediente'])->orderBy('id_movimiento', 'desc');

        if ($idIngrediente) {
            $query->where('id_ingrediente', $idIngrediente);
        }

        if ($limit) {
            $query->take((int)$limit);
        }

        return $query->get();
    }

    public function getMovimientos($idIngrediente = null, $limit = 100)
    {
        return $this->getAllMovimientos($idIngrediente, $limit);
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