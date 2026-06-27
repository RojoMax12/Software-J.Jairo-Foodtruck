<?php
namespace App\Services;

use App\Repositories\ProductoRepository;

class ProductoService
{
    protected $productoRepository;

    public function __construct(ProductoRepository $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    public function createProducto($data)
    {
        if (empty($data['nombre'])) {
            throw new \InvalidArgumentException('El nombre del producto es obligatorio.');
        }

        return $this->productoRepository->createProducto($data);
    }

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

    public function getProductosByTipo($tipo)
    {
        return $this->productoRepository->getProductosByTipo($tipo);
    }

    public function getPedidosByProductoId($id)
    {
        return $this->productoRepository->getPedidosByProductoId($id);
    }

    public function getIngredientesByProductoId($id)
    {
        return $this->productoRepository->getIngredientesByProductoId($id);
    }

    public function getOfertasByProductoId($id)
    {
        return $this->productoRepository->getOfertasByProductoId($id);
    }

    public function updateProducto($id, $data)
    {
        return $this->productoRepository->updateProducto($id, $data);
    }

    public function deleteProductoById($id)
    {
        return $this->productoRepository->deleteProductoById($id);
    }
}
