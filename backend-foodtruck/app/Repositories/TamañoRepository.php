<?php

namespace App\Repositories;
use App\Models\Tamaño;

class TamañoRepository
{
    public function createTamaño($data)
    {
        return Tamaño::create($data);
    }

    public function getTamaños()
    {
        return Tamaño::all();
    }

    public function updateTamaño($id, $data)
    {
        $tamaño = Tamaño::find($id);
        if ($tamaño) {
            $tamaño->update($data);
            return $tamaño;
        }
        return null;
    }

    public function deleteTamañoById($id)
    {
        $tamaño = Tamaño::find($id);
        if ($tamaño) {
            $tamaño->delete();
            return true;
        }
        return false;
    }
}