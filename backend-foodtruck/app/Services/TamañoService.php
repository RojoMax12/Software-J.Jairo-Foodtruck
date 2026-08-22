<?php

namespace App\Services;
use App\Repositories\TamañoRepository;

class TamañoService
{
    protected $tamañoRepository;

    public function __construct(TamañoRepository $tamañoRepository)
    {
        $this->tamañoRepository = $tamañoRepository;
    }

    public function createTamaño($data)
    {
        return $this->tamañoRepository->createTamaño($data);
    }

    public function getAllTamaños()
    {
        return $this->tamañoRepository->getAllTamaños();
    }

    public function getTamañoById($id)
    {
        return $this->tamañoRepository->getTamañoById($id);
    }

    public function updateTamaño($id, $data)
    {
        return $this->tamañoRepository->updateTamaño($id, $data);
    }

    public function deleteTamañoById($id)
    {
        return $this->tamañoRepository->deleteTamañoById($id);
    }
}