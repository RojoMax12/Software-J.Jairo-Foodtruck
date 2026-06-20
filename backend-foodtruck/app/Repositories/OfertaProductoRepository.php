<?php
namespace App\Repositories;
use App\Models\OfertaProducto;
# Repositorio OfertaProducto
class OfertaProductoRepository
{
    # Create
    public function createOfertaProducto($data)
    {
        return OfertaProducto::create($data);
    }

    # Geters
    public function getAllOfertaProductos()
    {
        return OfertaProducto::all();
    }

    public function getOfertaProductoById($id)
    {
        return OfertaProducto::find($id);
    }

    public function getOfertaProductosByProductoId($idProducto)
    {
        return OfertaProducto::where('id_productos', $idProducto)->get();
    }

    public function getOfertaProductosByOfertaId($idOferta)
    {
        return OfertaProducto::where('id_ofertas', $idOferta)->get();
    }

    # Seters
    public function updateOfertaProducto($id, $data)
    {
        $ofertaProducto = OfertaProducto::find($id);
        if ($ofertaProducto) {
            $ofertaProducto->update($data);
            return $ofertaProducto;
        }
        return null;
    }
    
    # Delete
    public function deleteOfertaProductoById($id)
    {
        $ofertaProducto = OfertaProducto::find($id);
        if ($ofertaProducto) {
            $ofertaProducto->delete();
            return true;
        }
        return false;
    }
}