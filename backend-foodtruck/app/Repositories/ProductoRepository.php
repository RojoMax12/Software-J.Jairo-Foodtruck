<?php
namespace App\Repositories;
use App\Models\Producto;

# Repositorio Producto
class ProductoRepository
{
    # Create
    public function createProducto($data)
    {
        return Producto::create($data);
    }

    # Geters
    public function getAllProductos()
    {
        return Producto::all();
    }

    public function getProductoById($id)
    {
        return Producto::find($id);
    }

    public function getProductoByNombre($nombre)
    {
        return Producto::where('nombre', $nombre)->first();
    }

    public function getProductosByTipo($tipo)
    {
        return Producto::where('tipo', $tipo)->get();
    }

    public function getPedidosByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->pedidos;
        }
        return null;
    }

    public function getIngredientesByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->producto_ingrediente;
        }
        return null;
    }

    public function getOfertasByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->ofertaProductos;
        }
        return null;
    }

    # Seters
    public function updateProducto($id, $data)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->update($data);
            return $producto;
        }
        return null;
    }

    # Delete
    public function deleteProductoById($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->delete();
            return true;
        }
        return false;
    }
}