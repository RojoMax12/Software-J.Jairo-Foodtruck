<?php

namespace App\Services;
use App\Repositories\MovimientosRepository;

class MovimientosServices
{
    protected $movimientosRepository;

    public function __construct(MovimientosRepository $movimientosRepository)
    {
        $this->movimientosRepository = $movimientosRepository;
    }

    public function createMovimiento($data)
    {
        return $this->movimientosRepository->createMovimiento($data);
    }

    public function getMovimientosById($id_movimiento)
    {
        return $this->movimientosRepository->getMovimientosById($id_movimiento);
    }

    public function updateMovimiento($id_movimiento, $data)
    {
        return $this->movimientosRepository->updateMovimiento($id_movimiento, $data);
    }

    public function deleteMovimientoById($id_movimiento)
    {
        return $this->movimientosRepository->deleteMovimientoById($id_movimiento);
    }
}