<?php

namespace App\Services;

use App\Repositories\ProductoRepository;

class ProductoServices
{
    protected $productoRepository;

    # Constructor de una instancia de ProductoRepository
    public function __construct(ProductoRepository $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    # Creators
    public function createProducto($data)
    {
        return $this->productoRepository->createProducto($data);
    }

    # Geters
    public function getAllProductos()
    {
        return $this->productoRepository->getAllProductos();
    }

    public function getProductoById($id)
    {
        return $this->productoRepository->getProductoById($id);
    }

    public function getProductoByNombre($nombre)
    {
        return $this->productoRepository->getProductoByNombre($nombre);
    }

    public function getProductosByCategoriaId($idCategoria)
    {
        return $this->productoRepository->getProductosByCategoriaId($idCategoria);
    }

    public function getProductosByFormatoId($idFormato)
    {
        return $this->productoRepository->getProductosByFormatoId($idFormato);
    }

    # Seters
    public function updateProducto($id, $data)
    {
        return $this->productoRepository->updateProducto($id, $data);
    }

    # Delete
    public function deleteProducto($id)
    {
        return $this->productoRepository->deleteProducto($id);
    }
}