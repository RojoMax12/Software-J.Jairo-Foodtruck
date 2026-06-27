<?php
namespace App\Services;
use App\Repositories\PedidoRepository;
# Servicio Pedido
// TODO: El modelo Pedido define producto_pedido() apuntando a ProductoPedido::class,
// clase que no existe entre los modelos provistos (probablemente debería ser
// Pedido_producto::class). Por eso este servicio no expone un getter basado en esa
// relación; usar PedidoProductoService->getPedidoProductosByPedidoId() como alternativa.
class PedidoService
{
    protected $pedidoRepository;

    public function __construct(PedidoRepository $pedidoRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
    }

    public function createPedido($data)
    {
        if (isset($data['total']) && $data['total'] < 0) {
            throw new \InvalidArgumentException('El total del pedido no puede ser negativo.');
        }

        return $this->pedidoRepository->createPedido($data);
    }

    public function getAllPedidos()
    {
        return $this->pedidoRepository->getAllPedidos();
    }

    public function getPedidoById($id)
    {
        return $this->pedidoRepository->getPedidoById($id);
    }

    public function getPedidosByUsuarioId($idUsuario)
    {
        return $this->pedidoRepository->getPedidosByUsuarioId($idUsuario);
    }

    public function getPedidosByEstadoId($idEstado)
    {
        return $this->pedidoRepository->getPedidosByEstadoId($idEstado);
    }

    public function getPedidosByEstadoPago($estadoPago)
    {
        return $this->pedidoRepository->getPedidosByEstadoPago($estadoPago);
    }

    public function getVentaByPedidoId($id)
    {
        return $this->pedidoRepository->getVentaByPedidoId($id);
    }

    public function updatePedido($id, $data)
    {
        if (isset($data['total']) && $data['total'] < 0) {
            throw new \InvalidArgumentException('El total del pedido no puede ser negativo.');
        }

        return $this->pedidoRepository->updatePedido($id, $data);
    }

    public function deletePedidoById($id)
    {
        return $this->pedidoRepository->deletePedidoById($id);
    }
}