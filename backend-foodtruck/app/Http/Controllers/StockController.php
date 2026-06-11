<?php

namespace App\Http\Controllers;

use App\Services\StockServices;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $stockServices;

    public function __construct(StockServices $stockServices)
    {
        $this->stockServices = $stockServices;
    }

    public function index()
    {
        return response()->json($this->stockServices->getAllStocks());
    }

    public function show($id)
    {
        return response()->json($this->stockServices->getStockById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cantidad_stock' => 'required|integer|min:0',
        ]);

        return response()->json($this->stockServices->createStock($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'cantidad_stock' => 'sometimes|required|integer|min:0',
        ]);

        return response()->json($this->stockServices->updateStock($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->stockServices->deleteStock($id));
    }
}