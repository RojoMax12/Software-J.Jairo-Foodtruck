<?php

namespace App\Services;

use App\Repositories\StockRepository;

class StockServices
{
    protected $stockRepository;

    # Constructor de una instancia de StockRepository
    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    # Creators
    public function createStock($data)
    {
        return $this->stockRepository->createStock($data);
    }

    # Geters
    public function getAllStocks()
    {
        return $this->stockRepository->getAllStocks();
    }

    public function getStockById($id)
    {
        return $this->stockRepository->getStockById($id);
    }

    public function getStockByCantidadStock($cantidadStock)
    {
        return $this->stockRepository->getStockByCantidadStock($cantidadStock);
    }

    # Seters
    public function updateStock($id, $data)
    {
        return $this->stockRepository->updateStock($id, $data);
    }

    # Delete
    public function deleteStock($id)
    {
        return $this->stockRepository->deleteStock($id);
    }
}