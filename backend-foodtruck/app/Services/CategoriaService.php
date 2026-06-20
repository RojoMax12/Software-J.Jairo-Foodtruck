<?php
namespace App\Services;
use App\Repositories\CategoriaRepository;
# Servicio Categoria
class CategoriaService
{
    protected $categoriaRepository;

    public function __construct(CategoriaRepository $categoriaRepository)
    {
        $this->categoriaRepository = $categoriaRepository;
    }

    public function createCategoria($data)
    {
        if (empty($data['nombre_categoria'])) {
            throw new \InvalidArgumentException('El nombre de la categoría es obligatorio.');
        }

        $existente = $this->categoriaRepository->getCategoriaByNombre($data['nombre_categoria']);
        if ($existente) {
            throw new \InvalidArgumentException('Ya existe una categoría con ese nombre.');
        }

        return $this->categoriaRepository->createCategoria($data);
    }

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

    public function getProductosByCategoriaId($id)
    {
        return $this->categoriaRepository->getProductosByCategoriaId($id);
    }

    public function updateCategoria($id, $data)
    {
        if (isset($data['nombre_categoria']) && empty($data['nombre_categoria'])) {
            throw new \InvalidArgumentException('El nombre de la categoría no puede estar vacío.');
        }

        return $this->categoriaRepository->updateCategoria($id, $data);
    }

    public function deleteCategoriaById($id)
    {
        return $this->categoriaRepository->deleteCategoriaById($id);
    }
}