<?php
namespace App\Repositories;
use App\Models\Oferta;

# Repositorio Oferta
class OfertaRepository
{
    # Create
    public function createOferta($data)
    {
        return Oferta::create($data);
    }

    # Geters
    public function getAllOfertas()
    {
        return Oferta::all();
    }

    public function getOfertaById($id)
    {
        return Oferta::find($id);
    }

    public function getOfertasByTipo($tipo)
    {
        return Oferta::where('tipo', $tipo)->get();
    }

    public function getOfertaProductosByOfertaId($id)
    {
        $oferta = Oferta::find($id);
        if ($oferta) {
            return $oferta->oferta_producto;
        }
        return null;
    }

    # Seters
    public function updateOferta($id, $data)
    {
        $oferta = Oferta::find($id);
        if ($oferta) {
            $oferta->update($data);
            return $oferta;
        }
        return null;
    }
    
    # Delete
    public function deleteOfertaById($id)
    {
        $oferta = Oferta::find($id);
        if ($oferta) {
            $oferta->delete();
            return true;
        }
        return false;
    }
}