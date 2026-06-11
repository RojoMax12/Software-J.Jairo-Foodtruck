<?php

namespace App\Services;

use App\Repositories\CategoriaRepository;

class CategoriaServices
{
	protected $categoriaRepository;

    # Constructor de una instancia de CategoriaRepository
	public function __construct(CategoriaRepository $categoriaRepository)
	{
		$this->categoriaRepository = $categoriaRepository;
	}

    # Creators
    public function createCategoria($data)
	{
		return $this->categoriaRepository->createCategoria($data);
	}

    # Geters
	public function getAllCategorias()
	{
		return $this->categoriaRepository->getAllCategorias();
	}

	public function getCategoriaById($id)
	{
		return $this->categoriaRepository->getCategoriaById($id);
	}

	public function getCategoriaByNombre($nombre)
	{
		return $this->categoriaRepository->getCategoriaByNombre($nombre);
	}

    # Seters
	public function updateCategoria($id, $data)
	{
		return $this->categoriaRepository->updateCategoria($id, $data);
	}

    # Delete
	public function deleteCategoria($id)
	{
		return $this->categoriaRepository->deleteCategoria($id);
	}
}
