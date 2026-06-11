<?php

namespace App\Services;

use App\Repositories\BodegaRepository;

class BodegaServices
{
	protected $bodegaRepository;

    # Constructor de una instancia de BodegaRepository
	public function __construct(BodegaRepository $bodegaRepository)
	{
		$this->bodegaRepository = $bodegaRepository;
	}

    # Creators
	public function createBodega($data)
	{
		return $this->bodegaRepository->createBodega($data);
	}

    # Geters 
	public function getAllBodegas()
	{
		return $this->bodegaRepository->getAllBodegas();
	}

	public function getBodegaById($id)
	{
		return $this->bodegaRepository->getBodegaById($id);
	}

	public function getBodegaByNombre($nombre)
	{
		return $this->bodegaRepository->getBodegaByNombre($nombre);
	}

	public function getBodegaByUbicacion($ubicacion)
	{
		return $this->bodegaRepository->getBodegaByUbicacion($ubicacion);
	}

	public function getProductosByBodegaId($id)
	{
		return $this->bodegaRepository->getProductosByBodegaId($id);
	}

    # Seters
	public function updateBodega($id, $data)
	{
		return $this->bodegaRepository->updateBodega($id, $data);
	}

    # Delete
	public function deleteBodegaById($id)
	{
		return $this->bodegaRepository->deleteBodegaById($id);
	}
}
