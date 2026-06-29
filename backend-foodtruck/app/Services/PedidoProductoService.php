<?php
namespace App\Services;
use App\Repositories\PedidoProductoRepository;
# Servicio PedidoProducto
class PedidoProductoService
{
    protected $pedidoProductoRepository;

    public function __construct(PedidoProductoRepository $pedidoProductoRepository)
    {
        $this->pedidoProductoRepository = $pedidoProductoRepository;
    }

    public function createPedidoProducto($data)
    {
        if (empty($data['id_pedido']) || empty($data['id_producto'])) {
            throw new \InvalidArgumentException('id_pedido e id_producto son obligatorios.');
        }

        return $this->pedidoProductoRepository->createPedidoProducto($data);
    }

    public function getAllPedidoProductos()
    {
        return $this->pedidoProductoRepository->getAllPedidoProductos();
    }

    public function getPedidoProductoById($id)
    {
        return $this->pedidoProductoRepository->getPedidoProductoById($id);
    }

    public function getPedidoProductosByPedidoId($idPedido)
    {
        return $this->pedidoProductoRepository->getPedidoProductosByPedidoId($idPedido);
    }

    public function getPedidoProductosByProductoId($idProducto)
    {
        return $this->pedidoProductoRepository->getPedidoProductosByProductoId($idProducto);
    }

    public function updatePedidoProducto($id, $data)
    {
        return $this->pedidoProductoRepository->updatePedidoProducto($id, $data);
    }

    public function deletePedidoProductoById($id)
    {
        return $this->pedidoProductoRepository->deletePedidoProductoById($id);
    }
}