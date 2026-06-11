<?php

namespace App\Repositories;
use App\Models\Bodega;

# Repositorio Bodega
class BodegaRepository
{
    # Create
    public function createBodega($data)
    {
        return Bodega::create($data);
    }

    # Geters 
    public function getAllBodegas()
    {
        return Bodega::all();
    }

    public function getBodegaById($id)
    {
        return Bodega::find($id);
    }

    public function getBodegaByNombre($nombre)
    {
        return Bodega::where('nombre_bodega', $nombre)->first();
    }   

    public function getBodegaByUbicacion($ubicacion)
    {
        return Bodega::where('ubicacion_bodega', $ubicacion)->first();
    }

    public function getProductosByBodegaId($id)
    {
        $bodega = Bodega::find($id);
        if ($bodega) {
            return $bodega->productos; // Asumiendo que hay una relación definida en el modelo Bodega
        }
        return null;
    }

    # Seters
    public function updateBodega($id, $data)
    {
        $bodega = Bodega::find($id);
        if ($bodega) {
            $bodega->update($data);
            return $bodega;
        }
        return null;
    }

    # Delete 
    public function deleteBodegaById($id)
    {
        $bodega = Bodega::find($id);
        if ($bodega) {
            $bodega->delete();
            return true;
        }
        return false;
    }
}