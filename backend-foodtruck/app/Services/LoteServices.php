<?php

namespace App\Services;

use App\Repositories\LoteRepository;

class LoteServices
{
    protected $loteRepository;

    # Constructor de una instancia de LoteRepository
    public function __construct(LoteRepository $loteRepository)
    {
        $this->loteRepository = $loteRepository;
    }

    # Creators
    public function createLote($data)
    {
        return $this->loteRepository->createLote($data);
    }    

    # Geters
    public function getAllLotes()
    {
        return $this->loteRepository->getAllLotes();
    }

    public function getLoteById($id)
    {
        return $this->loteRepository->getLoteById($id);
    }

    public function getLotesByProductoId($idProducto)
    {
        return $this->loteRepository->getLotesByProductoId($idProducto);
    }

    public function getLotesByStockId($idStock)
    {
        return $this->loteRepository->getLotesByStockId($idStock);
    }

    public function getLotesByBodegaId($idBodega)
    {
        return $this->loteRepository->getLotesByBodegaId($idBodega);
    }

    # Seters
    public function updateLote($id, $data)
    {
        return $this->loteRepository->updateLote($id, $data);
    }

    # Delete
    public function deleteLote($id)
    {
        return $this->loteRepository->deleteLote($id);
    }
}