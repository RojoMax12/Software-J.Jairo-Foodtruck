<?php

namespace App\Services;
use App\Repositories\Cotizacion_productoRepository;

class Cotizacion_productoServices
{
    protected $cotizacionProductoRepository;

    public function __construct(Cotizacion_productoRepository $cotizacionProductoRepository)
    {
        $this->cotizacionProductoRepository = $cotizacionProductoRepository;
    }

    public function createCotizacionProducto($data)
    {
        return $this->cotizacionProductoRepository->createCotizacionProducto($data);
    }

    public function getCotizacionProductoById($id)
    {
        return $this->cotizacionProductoRepository->getCotizacionProductoById($id);
    }

    public function getCotizacionProductosByCotizacionId($idCotizacion)
    {
        return $this->cotizacionProductoRepository->getCotizacionProductosByCotizacionId($idCotizacion);
    }

    public function getCotizacionProductosByProductoId($idProducto)
    {
        return $this->cotizacionProductoRepository->getCotizacionProductosByProductoId($idProducto);
    }

    public function updateCotizacionProducto($id, $data)
    {
        return $this->cotizacionProductoRepository->updateCotizacionProducto($id, $data);
    }

    public function deleteCotizacionProducto($id)
    {
        return $this->cotizacionProductoRepository->deleteCotizacionProducto($id);
    }

    public function getAllCotizacionProductos()
    {
        return $this->cotizacionProductoRepository->getAllCotizacionProductos();
    }
}