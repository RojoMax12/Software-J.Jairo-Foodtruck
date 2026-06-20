<?php
namespace App\Services;

use App\Repositories\ProductoIngredienteRepository;

class ProductoIngredienteService
{
    protected $productoIngredienteRepository;

    public function __construct(ProductoIngredienteRepository $productoIngredienteRepository)
    {
        $this->productoIngredienteRepository = $productoIngredienteRepository;
    }

    public function createProductoIngrediente($data)
    {
        if (empty($data['id_producto']) || empty($data['id_ingrediente'])) {
            throw new \InvalidArgumentException('El producto y el ingrediente son obligatorios.');
        }

        return $this->productoIngredienteRepository->createProductoIngrediente($data);
    }

    public function getAllProductoIngredientes()
    {
        return $this->productoIngredienteRepository->getAllProductoIngredientes();
    }

    public function getProductoIngredienteById($id)
    {
        return $this->productoIngredienteRepository->getProductoIngredienteById($id);
    }

    public function getProductoIngredientesByProductoId($idProducto)
    {
        return $this->productoIngredienteRepository->getProductoIngredientesByProductoId($idProducto);
    }

    public function getProductoIngredientesByIngredienteId($idIngrediente)
    {
        return $this->productoIngredienteRepository->getProductoIngredientesByIngredienteId($idIngrediente);
    }

    public function updateProductoIngrediente($id, $data)
    {
        return $this->productoIngredienteRepository->updateProductoIngrediente($id, $data);
    }

    public function deleteProductoIngredienteById($id)
    {
        return $this->productoIngredienteRepository->deleteProductoIngredienteById($id);
    }
}
