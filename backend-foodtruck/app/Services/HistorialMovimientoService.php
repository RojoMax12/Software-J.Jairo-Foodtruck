<?php

namespace App\Services;

use App\Repositories\HistorialMovimientoRepository;

class HistorialMovimientoService
{
    protected $repository;

    public function __construct(HistorialMovimientoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllHistorialMovimientos($limit = 200, $tipo = null, $search = null)
    {
        return $this->repository->getAllHistorialMovimientos($limit, $tipo, $search);
    }

    public function getHistorialMovimientoById($id)
    {
        return $this->repository->getHistorialMovimientoById($id);
    }

    public function createHistorialMovimiento($data)
    {
        return $this->repository->createHistorialMovimiento($data);
    }

    public function updateHistorialMovimiento($id, $data)
    {
        return $this->repository->updateHistorialMovimiento($id, $data);
    }

    public function deleteHistorialMovimientoById($id)
    {
        return $this->repository->deleteHistorialMovimientoById($id);
    }

    public function clearAllHistorial()
    {
        return $this->repository->clearAllHistorial();
    }
}