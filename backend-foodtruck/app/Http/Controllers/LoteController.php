<?php

namespace App\Http\Controllers;

use App\Services\LoteServices;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    protected $loteServices;

    public function __construct(LoteServices $loteServices)
    {
        $this->loteServices = $loteServices;
    }

    public function index()
    {
        return response()->json($this->loteServices->getAllLotes());
    }

    public function show($id)
    {
        return response()->json($this->loteServices->getLoteById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
            'id_stock' => 'required|integer|exists:stocks,id',
            'id_bodega' => 'required|integer|exists:bodegas,id',
            'cantidad_producto' => 'required|integer|min:0',
            'fecha_vencimiento' => 'required|date',
            'fecha_emision' => 'required|date',
        ]);

        return response()->json($this->loteServices->createLote($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_producto' => 'sometimes|required|integer|exists:productos,id',
            'id_stock' => 'sometimes|required|integer|exists:stocks,id',
            'id_bodega' => 'sometimes|required|integer|exists:bodegas,id',
            'cantidad_producto' => 'sometimes|required|integer|min:0',
            'fecha_vencimiento' => 'sometimes|required|date',
            'fecha_emision' => 'sometimes|required|date',
        ]);

        return response()->json($this->loteServices->updateLote($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->loteServices->deleteLote($id));
    }
}