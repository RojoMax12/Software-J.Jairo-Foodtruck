<?php

namespace App\Services;
use App\Repositories\VentaRepository;
use App\Models\Venta;

class VentaServices
{
    protected $ventaRepository;

    public function __construct(VentaRepository $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    public function getAllVentas()
    {
        return $this->ventaRepository->getAllVentas();
    }

    public function getVentaById($id)
    {
        return $this->ventaRepository->getVentaById($id);
    }

    public function createVenta($data)
    {
        return $this->ventaRepository->createVenta($data);
    }

    public function updateVenta($id, $data)
    {
        return $this->ventaRepository->updateVenta($id, $data);
    }

    public function deleteVenta($id)
    {
        return $this->ventaRepository->deleteVenta($id);
    }
}
