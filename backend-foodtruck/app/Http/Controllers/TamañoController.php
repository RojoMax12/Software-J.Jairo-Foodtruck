<?php

namespace App\Http\Controllers;
use App\Services\TamañoService;

class TamañoController extends Controller
{
    protected $tamañoService;

    public function __construct(TamañoService $tamañoService)
    {
        $this->tamañoService = $tamañoService;
    }

    public function createTamaño(Request $request)
    {
        $data = $request->all();
        $tamaño = $this->tamañoService->createTamaño($data);
        return response()->json($tamaño, 201);
    }

    public function getAllTamaños()
    {
        $tamaños = $this->tamañoService->getAllTamaños();
        return response()->json($tamaños);
    }

    public function getTamañoById($id)
    {
        $tamaño = $this->tamañoService->getTamañoById($id);
        return response()->json($tamaño);
    }

    public function updateTamaño(Request $request, $id)
    {
        $data = $request->all();
        $tamaño = $this->tamañoService->updateTamaño($id, $data);
        return response()->json($tamaño);
    }

    public function deleteTamañoById($id)
    {
        $this->tamañoService->deleteTamañoById($id);
        return response()->json(null, 204);
    }
}