<?php

namespace App\Repositories;
use App\Models\Estado_pago;

class EstadoPagoRepository
{
    public function createEstadoPago($data)
    {
        return Estado_pago::create($data);
    }

    public function getEstadoPagos()
    {
        return Estado_pago::all();
    }

    public function updateEstadoPago($id, $data)
    {
        $estadoPago = Estado_pago::find($id);
        if ($estadoPago) {
            $estadoPago->update($data);
            return $estadoPago;
        }
        return null;
    }

    public function deleteEstadoPagoById($id)
    {
        $estadoPago = Estado_pago::find($id);
        if ($estadoPago) {
            $estadoPago->delete();
            return true;
        }
        return false;
    }
}
