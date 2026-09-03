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
        return Producto::with(['categoria', 'tamaños', 'ingredientes.ingrediente', 'ingredientes.tamaño'])->get();
    }

    public function getProductoById($id)
    {
        return Producto::with(['categoria', 'tamaños', 'ingredientes.ingrediente', 'ingredientes.tamaño'])->find($id);
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
            return $producto->detalles()->with('pedido')->get()->pluck('pedido')->filter()->unique('id_pedido')->values();
        }
        return null;
    }

    public function getIngredientesByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->ingredientes;
        }
        return null;
    }

    public function getOfertasByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->ofertas;
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