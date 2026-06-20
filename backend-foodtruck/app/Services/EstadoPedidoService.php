<?php
namespace App\Services;
use App\Repositories\EstadoPedidoRepository;
# Servicio EstadoPedido
// TODO: El modelo EstadoPedido usa 'id_pedido' como primaryKey propia, lo cual
// es inusual para una tabla de estados (debería ser su propio 'id' o 'id_estado_pedido').
// Esto puede causar colisiones de lógica con la FK 'id_estado_pedido' de la tabla pedidos.
class EstadoPedidoService
{
    protected $estadoPedidoRepository;

    public function __construct(EstadoPedidoRepository $estadoPedidoRepository)
    {
        $this->estadoPedidoRepository = $estadoPedidoRepository;
    }

    public function createEstadoPedido($data)
    {
        if (empty($data['nombre'])) {
            throw new \InvalidArgumentException('El nombre del estado es obligatorio.');
        }

        return $this->estadoPedidoRepository->createEstadoPedido($data);
    }

    public function getAllEstadosPedido()
    {
        return $this->estadoPedidoRepository->getAllEstadosPedido();
    }

    public function getEstadoPedidoById($id)
    {
        return $this->estadoPedidoRepository->getEstadoPedidoById($id);
    }

    public function getEstadoPedidoByNombre($nombre)
    {
        return $this->estadoPedidoRepository->getEstadoPedidoByNombre($nombre);
    }

    public function getPedidosByEstadoId($id)
    {
        return $this->estadoPedidoRepository->getPedidosByEstadoId($id);
    }

    public function updateEstadoPedido($id, $data)
    {
        if (isset($data['nombre']) && empty($data['nombre'])) {
            throw new \InvalidArgumentException('El nombre del estado no puede estar vacío.');
        }

        return $this->estadoPedidoRepository->updateEstadoPedido($id, $data);
    }

    public function deleteEstadoPedidoById($id)
    {
        return $this->estadoPedidoRepository->deleteEstadoPedidoById($id);
    }
}