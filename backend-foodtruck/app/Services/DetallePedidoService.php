<?php

namespace App\Services;
use App\Repositories\DetallePedidoRepository;

class DetallePedidoService
{
    protected $detallePedidoRepository;

    public function __construct(DetallePedidoRepository $detallePedidoRepository)
    {
        $this->detallePedidoRepository = $detallePedidoRepository;
    }

    public function createDetallePedido($data)
    {
        return $this->detallePedidoRepository->createDetallePedido($data);
    }

    public function getDetallePedidosByPedidoId($id_pedido)
    {
        return $this->detallePedidoRepository->getDetallePedidosByPedidoId($id_pedido);
    }

    public function updateDetallePedido($id, $data)
    {
        return $this->detallePedidoRepository->updateDetallePedido($id, $data);
    }

    public function deleteDetallePedidoById($id)
    {
        return $this->detallePedidoRepository->deleteDetallePedidoById($id);
    }
}