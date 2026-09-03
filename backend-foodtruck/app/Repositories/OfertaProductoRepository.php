<?php
namespace App\Repositories;
use App\Models\Oferta_producto;
# Repositorio OfertaProducto
class OfertaProductoRepository
{
    # Create
    public function createOfertaProducto($data)
    {
        return Oferta_producto::create($data);
    }

    # Geters
    public function getAllOfertaProductos()
    {
        return Oferta_producto::all();
    }

    public function getOfertaProductoById($id)
    {
        return Oferta_producto::find($id);
    }

    public function getOfertaProductosByProductoId($idProducto)
    {
        return Oferta_producto::where('id_productos', $idProducto)->get();
    }

    public function getOfertaProductosByOfertaId($idOferta)
    {
        return Oferta_producto::where('id_oferta', $idOferta)->get();
    }

    # Seters
    public function updateOfertaProducto($id, $data)
    {
        $ofertaProducto = Oferta_producto::find($id);
        if ($ofertaProducto) {
            $ofertaProducto->update($data);
            return $ofertaProducto;
        }
        return null;
    }
    
    # Delete
    public function deleteOfertaProductoById($id)
    {
        $ofertaProducto = Oferta_producto::find($id);
        if ($ofertaProducto) {
            $ofertaProducto->delete();
            return true;
        }
        return false;
    }
}