<?php
namespace App\Services;
use App\Repositories\OfertaProductoRepository;
# Servicio OfertaProducto
class OfertaProductoService
{
    protected $ofertaProductoRepository;

    public function __construct(OfertaProductoRepository $ofertaProductoRepository)
    {
        $this->ofertaProductoRepository = $ofertaProductoRepository;
    }

    public function createOfertaProducto($data)
    {
        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            throw new \InvalidArgumentException('El precio de oferta no puede ser negativo.');
        }

        return $this->ofertaProductoRepository->createOfertaProducto($data);
    }

    public function getAllOfertaProductos()
    {
        return $this->ofertaProductoRepository->getAllOfertaProductos();
    }

    public function getOfertaProductoById($id)
    {
        return $this->ofertaProductoRepository->getOfertaProductoById($id);
    }

    public function getOfertaProductosByProductoId($idProducto)
    {
        return $this->ofertaProductoRepository->getOfertaProductosByProductoId($idProducto);
    }

    public function getOfertaProductosByOfertaId($idOferta)
    {
        return $this->ofertaProductoRepository->getOfertaProductosByOfertaId($idOferta);
    }

    public function updateOfertaProducto($id, $data)
    {
        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            throw new \InvalidArgumentException('El precio de oferta no puede ser negativo.');
        }

        return $this->ofertaProductoRepository->updateOfertaProducto($id, $data);
    }

    public function deleteOfertaProductoById($id)
    {
        return $this->ofertaProductoRepository->deleteOfertaProductoById($id);
    }
}