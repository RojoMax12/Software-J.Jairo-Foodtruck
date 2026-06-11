<?php

namespace App\Repositories;
use App\Models\Formato;

# Repositorio Formato
class FormatoRepository
{
    # Create
    public function createFormato($data)
    {
        return Formato::create($data);
    }

    # Geters
    public function getAllFormatos()
    {
        return Formato::all();
    }

    public function getFormatoById($id)
    {
        return Formato::find($id);
    }

    public function getFormatoByNombre($nombre)
    {
        return Formato::where('nombre_formato', $nombre)->first();
    }

    # Seters
    public function updateFormato($id, $data)
    {
        $formato = Formato::find($id);
        if ($formato) {
            $formato->update($data);
            return $formato;
        }
        return null;
    }

    # Delete
    public function deleteFormato($id)
    {
        $formato = Formato::find($id);
        if ($formato) {
            $formato->delete();
            return true;
        }
        return false;
    }
}