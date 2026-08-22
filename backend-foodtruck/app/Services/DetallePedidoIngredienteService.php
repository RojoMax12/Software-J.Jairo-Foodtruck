<?php

namespace App\Services;
use App\Repositories\DetallePedidoIngredienteRepository;

class DetallePedidoIngredienteService
{
    protected $detallePedidoIngredienteRepository;

    public function __construct(DetallePedidoIngredienteRepository $detallePedidoIngredienteRepository)
    {
        $this->detallePedidoIngredienteRepository = $detallePedidoIngredienteRepository;
    }

    public function createDetallePedidoIngrediente($data)
    {
        return $this->detallePedidoIngredienteRepository->createDetallePedidoIngrediente($data);
    }

    public function getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido)
    {
        return $this->detallePedidoIngredienteRepository->getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido);
    }

    public function updateDetallePedidoIngrediente($id, $data)
    {
        return $this->detallePedidoIngredienteRepository->updateDetallePedidoIngrediente($id, $data);
    }

    public function deleteDetallePedidoIngredienteById($id)
    {
        return $this->detallePedidoIngredienteRepository->deleteDetallePedidoIngredienteById($id);
    }
}   