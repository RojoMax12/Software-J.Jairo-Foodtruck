<?php

namespace App\Http\Controllers;

use App\Services\IngredienteService;
use Illuminate\Http\Request;

class IngredienteController extends Controller
{
    protected $ingredienteService;

    public function __construct(IngredienteService $ingredienteService)
    {
        $this->ingredienteService = $ingredienteService;
    }

    public function index()
    {
        return response()->json($this->ingredienteService->getAllIngredientes());
    }

    public function show($id)
    {
        return response()->json($this->ingredienteService->getIngredienteById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->ingredienteService->createIngrediente($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->ingredienteService->updateIngrediente($id, $data));
    }

    public function destroy($id)
    {
        $this->ingredienteService->deleteIngredienteById($id);
        return response()->json(null, 204);
    }
}
