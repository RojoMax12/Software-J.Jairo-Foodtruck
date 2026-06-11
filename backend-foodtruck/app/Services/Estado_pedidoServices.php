<?php

namespace App\Services;
use App\Repositories\Estado_pedidoRepository;

class Estado_pedidoServices
{
    protected $estadoPedidoRepository;

    public function __construct(Estado_pedidoRepository $estadoPedidoRepository)
    {
        $this->estadoPedidoRepository = $estadoPedidoRepository;
    }

    public function getAllEstadosPedido()
    {
        return $this->estadoPedidoRepository->getAllEstadosPedido();
    }

    public function getEstadoPedidoById($id)
    {
        return $this->estadoPedidoRepository->getEstadoPedidoById($id);
    }

    public function createEstadoPedido($data)
    {
        return $this->estadoPedidoRepository->createEstadoPedido($data);
    }

    public function updateEstadoPedido($id, $data)
    {
        return $this->estadoPedidoRepository->updateEstadoPedido($id, $data);
    }

    public function deleteEstadoPedido($id)
    {
        return $this->estadoPedidoRepository->deleteEstadoPedido($id);
    }
}