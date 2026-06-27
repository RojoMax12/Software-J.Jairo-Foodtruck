<?php
namespace App\Services;

use App\Repositories\VentaRepository;

class VentaService
{
    protected $ventaRepository;

    public function __construct(VentaRepository $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    public function createVenta($data)
    {
        return $this->ventaRepository->createVenta($data);
    }

    public function getAllVentas()
    {
        return $this->ventaRepository->getAllVentas();
    }

    public function getVentaById($id)
    {
        return $this->ventaRepository->getVentaById($id);
    }

    public function getVentaByPedidoId($idPedido)
    {
        return $this->ventaRepository->getVentaByPedidoId($idPedido);
    }

    public function getCajasByVentaId($id)
    {
        return $this->ventaRepository->getCajasByVentaId($id);
    }

    public function updateVenta($id, $data)
    {
        return $this->ventaRepository->updateVenta($id, $data);
    }

    public function deleteVentaById($id)
    {
        return $this->ventaRepository->deleteVentaById($id);
    }
}
