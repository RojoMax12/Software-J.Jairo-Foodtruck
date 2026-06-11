<?php

namespace App\Http\Controllers;

use App\Services\CategoriaServices;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    protected $categoriaServices;

    public function __construct(CategoriaServices $categoriaServices)
    {
        $this->categoriaServices = $categoriaServices;
    }

    public function index()
    {
        return response()->json($this->categoriaServices->getAllCategorias());
    }

    public function show($id)
    {
        return response()->json($this->categoriaServices->getCategoriaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_categoria' => 'required|string|max:255',
            'descripcion_categoria' => 'required|string|max:255',
        ]);

        return response()->json($this->categoriaServices->createCategoria($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_categoria' => 'sometimes|required|string|max:255',
            'descripcion_categoria' => 'sometimes|required|string|max:255',
        ]);

        return response()->json($this->categoriaServices->updateCategoria($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->categoriaServices->deleteCategoria($id));
    }
}