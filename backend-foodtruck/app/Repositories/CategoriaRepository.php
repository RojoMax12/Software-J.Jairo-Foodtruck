<?php
namespace App\Repositories;
use App\Models\Categoria;

# Repositorio Categoria
class CategoriaRepository
{
    # Create
    public function createCategoria($data)
    {
        return Categoria::create($data);
    }

    # Geters
    public function getAllCategorias()
    {
        return Categoria::all();
    }

    public function getCategoriaById($id)
    {
        return Categoria::find($id);
    }

    public function getCategoriaByNombre($nombre)
    {
        return Categoria::where('nombre_categoria', $nombre)->first();
    }

    public function getProductosByCategoriaId($id)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            return $categoria->productos;
        }
        return null;
    }

    # Seters
    public function updateCategoria($id, $data)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->update($data);
            return $categoria;
        }
        return null;
    }
    
    # Delete
    public function deleteCategoriaById($id)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->delete();
            return true;
        }
        return false;
    }
}