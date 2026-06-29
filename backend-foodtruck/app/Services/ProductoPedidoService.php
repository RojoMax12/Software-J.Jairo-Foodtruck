<?php
namespace App\Services;

use App\Repositories\ProductoPedidoRepository;

class ProductoPedidoService
{
    protected $productoPedidoRepository;

    public function __construct(ProductoPedidoRepository $productoPedidoRepository)
    {
        $this->productoPedidoRepository = $productoPedidoRepository;
    }

    public function createProductoPedido($data)
    {
        if (empty($data['id_producto']) || empty($data['id_pedido'])) {
            throw new \InvalidArgumentException('El producto y el pedido son obligatorios.');
        }

        return $this->productoPedidoRepository->createProductoPedido($data);
    }

    public function getAllProductoPedidos()
    {
        return $this->productoPedidoRepository->getAllProductoPedidos();
    }

    public function getProductoPedidoById($id)
    {
        return $this->productoPedidoRepository->getProductoPedidoById($id);
    }

    public function getProductoPedidosByProductoId($idProducto)
    {
        return $this->productoPedidoRepository->getProductoPedidosByProductoId($idProducto);
    }

    public function getProductoPedidosByPedidoId($idPedido)
    {
        return $this->productoPedidoRepository->getProductoPedidosByPedidoId($idPedido);
    }

    public function updateProductoPedido($id, $data)
    {
        return $this->productoPedidoRepository->updateProductoPedido($id, $data);
    }

    public function deleteProductoPedidoById($id)
    {
        return $this->productoPedidoRepository->deleteProductoPedidoById($id);
    }
}
