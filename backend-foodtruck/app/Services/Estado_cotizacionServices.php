<?php

namespace App\Services;
use App\Repositories\Estado_cotizacionRepository;


class Estado_cotizacionServices{

    protected $estadoCotizacionRepository;

    public function __construct(Estado_cotizacionRepository $estadoCotizacionRepository)
    {
        $this->estadoCotizacionRepository = $estadoCotizacionRepository;
    }

    public function getAllEstadosCotizacion()
    {
        return $this->estadoCotizacionRepository->getAllEstado_cotizacion();
    
    }

    public function getEstadoCotizacionById($id)
    {
        return $this->estadoCotizacionRepository->getEstado_cotizacionById($id);
    }

    public function createEstadoCotizacion($data)
    {

    return $this->estadoCotizacionRepository->createEstado_cotizacion($data);

    }

    public function updateEstadoCotizacion($id, $data)
    {

    return $this->estadoCotizacionRepository->updateEstado_cotizacion($id, $data);

    }

    public function deleteEstadoCotizacion($id)
    {

    return $this->estadoCotizacionRepository->deleteEstado_cotizacion($id);
    
    }





}