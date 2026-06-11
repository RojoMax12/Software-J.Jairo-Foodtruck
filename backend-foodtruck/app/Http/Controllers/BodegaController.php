<?php

namespace App\Http\Controllers;

use App\Services\BodegaServices;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    protected $bodegaServices;

    public function __construct(BodegaServices $bodegaServices)
    {
        $this->bodegaServices = $bodegaServices;
    }

    public function index()
    {
        return response()->json($this->bodegaServices->getAllBodegas());
    }

    public function show($id)
    {
        return response()->json($this->bodegaServices->getBodegaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_bodega' => 'required|string|max:255',
            'ubicacion_bodega' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer|min:0',
        ]);

        return response()->json($this->bodegaServices->createBodega($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_bodega' => 'sometimes|required|string|max:255',
            'ubicacion_bodega' => 'sometimes|required|string|max:255',
            'cantidad_productos' => 'sometimes|required|integer|min:0',
        ]);

        return response()->json($this->bodegaServices->updateBodega($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->bodegaServices->deleteBodegaById($id));
    }
}