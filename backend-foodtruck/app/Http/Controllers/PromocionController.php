<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Support\CatalogCache;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index(Request $request)
    {
        $data = CatalogCache::rememberPromotions(fn () => Promocion::with(['producto:id_producto,nombre', 'tamano:id_tamaño,nombre'])
            ->latest()
            ->get()
        );
        return CatalogCache::respondWithEtag($request, $data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_tamaño' => ['nullable', 'integer', 'exists:tamaños,id_tamaño'],
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_promocional' => ['required', 'integer', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $promocion = Promocion::create($data)->load(['producto:id_producto,nombre', 'tamano:id_tamaño,nombre']);
        CatalogCache::invalidate();

        return response()->json($promocion, 201);
    }

    public function update(Request $request, Promocion $promocion)
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_tamaño' => ['nullable', 'integer', 'exists:tamaños,id_tamaño'],
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_promocional' => ['required', 'integer', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $promocion->update($data);
        CatalogCache::invalidate();

        return response()->json(
            $promocion->load(['producto:id_producto,nombre', 'tamano:id_tamaño,nombre'])
        );
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();
        CatalogCache::invalidate();
        return response()->json(null, 204);
    }
}
