<?php
namespace App\Services;

use App\Models\HistorialMovimiento;
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

        $cat = $this->categoriaRepository->createCategoria($data);
        if ($cat) {
            HistorialMovimiento::registrar(
                'categoria',
                'crear',
                'Nueva categoría creada',
                $cat->nombre_categoria,
                'Creada en catálogo de menú'
            );
        }
        return $cat;
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

        $catAnterior = $this->categoriaRepository->getCategoriaById($id);
        $cat = $this->categoriaRepository->updateCategoria($id, $data);
        if ($cat) {
            HistorialMovimiento::registrar(
                'categoria',
                'editar',
                'Categoría modificada',
                $cat->nombre_categoria ?? ($catAnterior->nombre_categoria ?? "Categoría #$id"),
                'Modificación de categoría en el menú'
            );
        }
        return $cat;
    }

    public function deleteCategoriaById($id)
    {
        $cat = $this->categoriaRepository->getCategoriaById($id);
        $nombre = $cat ? $cat->nombre_categoria : "Categoría #$id";
        $deleted = $this->categoriaRepository->deleteCategoriaById($id);
        if ($deleted) {
            HistorialMovimiento::registrar(
                'categoria',
                'eliminar',
                'Categoría eliminada',
                $nombre,
                'Eliminada del catálogo'
            );
        }
        return $deleted;
    }
}