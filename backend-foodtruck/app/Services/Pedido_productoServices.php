<?php

namespace App\Services;

use App\Repositories\Pedido_productoRepository;

class Pedido_productoServices
{
    protected $pedidoProductoRepository;

    # Constructor de una instancia de Pedido_productoRepository
    public function __construct(Pedido_productoRepository $pedidoProductoRepository)
    {
        $this->pedidoProductoRepository = $pedidoProductoRepository;
    }

    # Creators
    public function createPedidoProducto($data)
    {
        return $this->pedidoProductoRepository->createPedidoProducto($data);
    }

    # Geters
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

    # Seters
    public function updatePedidoProducto($id, $data)
    {
        return $this->pedidoProductoRepository->updatePedidoProducto($id, $data);
    }

    # Delete    
    public function deletePedidoProducto($id)
    {
        return $this->pedidoProductoRepository->deletePedidoProducto($id);
    }
}