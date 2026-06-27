<?php
namespace App\Repositories;
use App\Models\Producto_ingrediente;

# Repositorio ProductoIngrediente
class ProductoIngredienteRepository
{
    # Create
    public function createProductoIngrediente($data)
    {
        return Producto_ingrediente::create($data);
    }

    # Geters
    public function getAllProductoIngredientes()
    {
        return Producto_ingrediente::all();
    }

    public function getProductoIngredienteById($id)
    {
        return Producto_ingrediente::find($id);
    }

    public function getProductoIngredientesByProductoId($idProducto)
    {
        return Producto_ingrediente::where('id_producto', $idProducto)->get();
    }

    public function getProductoIngredientesByIngredienteId($idIngrediente)
    {
        return Producto_ingrediente::where('id_ingrediente', $idIngrediente)->get();
    }

    # Seters
    public function updateProductoIngrediente($id, $data)
    {
        $productoIngrediente = Producto_ingrediente::find($id);
        if ($productoIngrediente) {
            $productoIngrediente->update($data);
            return $productoIngrediente;
        }
        return null;
    }
    
    # Delete
    public function deleteProductoIngredienteById($id)
    {
        $productoIngrediente = Producto_ingrediente::find($id);
        if ($productoIngrediente) {
            $productoIngrediente->delete();
            return true;
        }
        return false;
    }
}