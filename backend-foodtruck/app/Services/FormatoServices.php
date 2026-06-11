<?php

namespace App\Services;

use App\Repositories\FormatoRepository;

class FormatoServices
{
    protected $formatoRepository;

    # Constructor de una instancia de FormatoRepository
    public function __construct(FormatoRepository $formatoRepository)
    {
        $this->formatoRepository = $formatoRepository;
    }

    # Creators
    public function createFormato($data)
    {
        return $this->formatoRepository->createFormato($data);
    }

    # Geters
    public function getAllFormatos()
    {
        return $this->formatoRepository->getAllFormatos();
    }

    public function getFormatoById($id)
    {
        return $this->formatoRepository->getFormatoById($id);
    }

    public function getFormatoByNombre($nombre)
    {
        return $this->formatoRepository->getFormatoByNombre($nombre);
    }

    # Seters
    public function updateFormato($id, $data)
    {
        return $this->formatoRepository->updateFormato($id, $data);
    }

    # Delete
    public function deleteFormato($id)
    {
        return $this->formatoRepository->deleteFormato($id);
    }
}