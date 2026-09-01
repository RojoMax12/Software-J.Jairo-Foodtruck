<?php

namespace App\Http\Controllers;

use App\Services\TamañoService;
use Illuminate\Http\Request;

class TamañoController extends Controller
{
    protected $tamañoService;

    public function __construct(TamañoService $tamañoService)
    {
        $this->tamañoService = $tamañoService;
    }

    public function index()
    {
        return response()->json($this->tamañoService->getAllTamaños());
    }

    public function show($id)
    {
        return response()->json($this->tamañoService->getTamañoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $tamaño = $this->tamañoService->createTamaño($data);
        return response()->json($tamaño, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $tamaño = $this->tamañoService->updateTamaño($id, $data);
        return response()->json($tamaño);
    }

    public function destroy($id)
    {
        $this->tamañoService->deleteTamañoById($id);
        return response()->json(null, 204);
    }

    public function createTamaño(Request $request)
    {
        return $this->store($request);
    }

    public function getAllTamaños()
    {
        return $this->index();
    }

    public function getTamañoById($id)
    {
        return $this->show($id);
    }

    public function updateTamaño(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    public function deleteTamañoById($id)
    {
        return $this->destroy($id);
    }
}