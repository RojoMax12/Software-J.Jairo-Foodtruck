<?php

namespace App\Services;
use App\Repositories\EstadoPagoRepository;

class EstadoPagoService
{
    protected $estadoPagoRepository;

    public function __construct(EstadoPagoRepository $estadoPagoRepository)
    {
        $this->estadoPagoRepository = $estadoPagoRepository;
    }

    public function createEstadoPago($data)
    {
        return $this->estadoPagoRepository->createEstadoPago($data);
    }

    public function getEstadoPagos()
    {
        return $this->estadoPagoRepository->getEstadoPagos();
    }

    public function getEstadoPagoById($id)
    {
        return $this->estadoPagoRepository->getEstadoPagoById($id);
    }

    public function updateEstadoPago($id, $data)
    {
        return $this->estadoPagoRepository->updateEstadoPago($id, $data);
    }

    public function deleteEstadoPagoById($id)
    {
        return $this->estadoPagoRepository->deleteEstadoPagoById($id);
    }
}