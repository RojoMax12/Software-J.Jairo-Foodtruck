<?php

namespace App\Repositories;
use App\Models\Stock;

# Repositorio Stock
class StockRepository
{
    # Create
    public function createStock($data)
    {
        return Stock::create($data);
    }

    # Geters
    public function getAllStocks()
    {
        return Stock::all();
    }

    public function getStockById($id)
    {
        return Stock::find($id);
    }

    public function getStockByCantidadStock($cantidadStock)
    {
        return Stock::where('cantidad_stock', $cantidadStock)->first();
    }

    # Seters
    public function updateStock($id, $data)
    {
        $stock = Stock::find($id);
        if ($stock) {
            $stock->update($data);
            return $stock;
        }
        return null;
    }

    # Delete
    public function deleteStock($id)
    {
        $stock = Stock::find($id);
        if ($stock) {
            $stock->delete();
            return true;
        }
        return false;
    }
}