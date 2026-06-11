<?php

namespace App\Repositories;
use App\Models\Lote;

# Repositorio Lote
class LoteRepository
{
    # Create
    public function createLote($data)
    {
        return Lote::create($data);
    }

    # Geters
    public function getAllLotes()
    {
        return Lote::all();
    }

    public function getLoteById($id)
    {
        return Lote::find($id);
    }

    public function getLotesByProductoId($idProducto)
    {
        return Lote::where('id_producto', $idProducto)->get();
    }

    public function getLotesByStockId($idStock)
    {
        return Lote::where('id_stock', $idStock)->get();
    }

    public function getLotesByBodegaId($idBodega)
    {
        return Lote::where('id_bodega', $idBodega)->get();
    }

    # Seters
    public function updateLote($id, $data)
    {
        $lote = Lote::find($id);
        if ($lote) {
            $lote->update($data);
            return $lote;
        }
        return null;
    }

    # Delete
    public function deleteLote($id)
    {
        $lote = Lote::find($id);
        if ($lote) {
            $lote->delete();
            return true;
        }
        return false;
    }
}