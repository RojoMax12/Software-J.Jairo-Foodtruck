<?php

namespace App\Services;
use App\Repositories\ProductoTamañoRepository;

class ProductoTamañoService
{
    protected $productoTamañoRepository;

    public function __construct(ProductoTamañoRepository $productoTamañoRepository)
    {
        $this->productoTamañoRepository = $productoTamañoRepository;
    }

    public function createProductoTamaño($data)
    {
        return $this->productoTamañoRepository->createProductoTamaño($data);
    }

    public function getProductoTamañosByProductoId($id_producto)
    {
        return $this->productoTamañoRepository->getProductoTamañosByProductoId($id_producto);
    }

    public function updateProductoTamaño($id, $data)
    {
        return $this->productoTamañoRepository->updateProductoTamaño($id, $data);
    }

    public function deleteProductoTamañoById($id)
    {
        return $this->productoTamañoRepository->deleteProductoTamañoById($id);
    }
}