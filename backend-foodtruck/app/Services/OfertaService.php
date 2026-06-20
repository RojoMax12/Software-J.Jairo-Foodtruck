<?php
namespace App\Services;
use App\Repositories\OfertaRepository;
# Servicio Oferta
class OfertaService
{
    protected $ofertaRepository;

    public function __construct(OfertaRepository $ofertaRepository)
    {
        $this->ofertaRepository = $ofertaRepository;
    }

    public function createOferta($data)
    {
        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            throw new \InvalidArgumentException('El precio de oferta no puede ser negativo.');
        }

        return $this->ofertaRepository->createOferta($data);
    }

    public function getAllOfertas()
    {
        return $this->ofertaRepository->getAllOfertas();
    }

    public function getOfertaById($id)
    {
        return $this->ofertaRepository->getOfertaById($id);
    }

    public function getOfertasByTipo($tipo)
    {
        return $this->ofertaRepository->getOfertasByTipo($tipo);
    }

    public function getOfertaProductosByOfertaId($id)
    {
        return $this->ofertaRepository->getOfertaProductosByOfertaId($id);
    }

    public function updateOferta($id, $data)
    {
        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            throw new \InvalidArgumentException('El precio de oferta no puede ser negativo.');
        }

        return $this->ofertaRepository->updateOferta($id, $data);
    }

    public function deleteOfertaById($id)
    {
        return $this->ofertaRepository->deleteOfertaById($id);
    }
}