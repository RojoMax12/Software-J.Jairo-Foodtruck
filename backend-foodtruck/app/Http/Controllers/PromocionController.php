<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index()
    {
        return response()->json(Promocion::with('producto:id_producto,nombre')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_promocional' => ['required', 'integer', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        return response()->json(Promocion::create($data)->load('producto:id_producto,nombre'), 201);
    }

    public function update(Request $request, Promocion $promocion)
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_promocional' => ['required', 'integer', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $promocion->update($data);
        return response()->json($promocion->load('producto:id_producto,nombre'));
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();
        return response()->json(null, 204);
    }
}