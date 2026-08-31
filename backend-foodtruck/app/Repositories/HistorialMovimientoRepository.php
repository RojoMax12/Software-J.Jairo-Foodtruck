<?php

namespace App\Repositories;

use App\Models\HistorialMovimiento;

class HistorialMovimientoRepository
{
    public function getAllHistorialMovimientos($limit = 200, $tipo = null, $search = null)
    {
        $query = HistorialMovimiento::query();

        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('entidad', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%")
                  ->orWhere('detalle', 'LIKE', "%{$search}%")
                  ->orWhere('usuario', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('fecha', 'desc')
            ->orderBy('id_historial', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getHistorialMovimientoById($id)
    {
        return HistorialMovimiento::find($id);
    }

    public function createHistorialMovimiento($data)
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = now();
        }
        return HistorialMovimiento::create($data);
    }

    public function updateHistorialMovimiento($id, $data)
    {
        $mov = HistorialMovimiento::find($id);
        if ($mov) {
            $mov->update($data);
            return $mov;
        }
        return null;
    }

    public function deleteHistorialMovimientoById($id)
    {
        $mov = HistorialMovimiento::find($id);
        if ($mov) {
            $mov->delete();
            return true;
        }
        return false;
    }

    public function clearAllHistorial()
    {
        return HistorialMovimiento::truncate();
    }
}