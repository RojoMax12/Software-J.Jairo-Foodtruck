<?php

namespace App\Http\Controllers;

use App\Services\FormatoServices;
use Illuminate\Http\Request;

class FormatoController extends Controller
{
    protected $formatoServices;

    public function __construct(FormatoServices $formatoServices)
    {
        $this->formatoServices = $formatoServices;
    }

    public function index()
    {
        return response()->json($this->formatoServices->getAllFormatos());
    }

    public function show($id)
    {
        return response()->json($this->formatoServices->getFormatoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_formato' => 'required|string|max:255',
        ]);

        return response()->json($this->formatoServices->createFormato($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_formato' => 'sometimes|required|string|max:255',
        ]);

        return response()->json($this->formatoServices->updateFormato($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->formatoServices->deleteFormato($id));
    }
}