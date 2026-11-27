<?php

namespace App\Http\Controllers;

use App\Models\HistorialMovimiento;
use App\Models\Producto;
use App\Models\Promocion;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'precio_promocional' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $errorMsg = $this->validarReglasNegocioPromocion($data);
        if ($errorMsg !== null) {
            return response()->json(['message' => $errorMsg], 422);
        }

        $promocion = Promocion::create($data)->load(['producto:id_producto,nombre', 'tamano:id_tamaño,nombre']);
        CatalogCache::invalidate();

        return response()->json($promocion, 201);
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'reemplazar_conflictos' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1', 'max:60'],
            'items.*.id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'items.*.id_tamaño' => ['nullable', 'integer', 'exists:tamaños,id_tamaño'],
            'items.*.titulo' => ['required', 'string', 'max:120'],
            'items.*.descripcion' => ['nullable', 'string', 'max:500'],
            'items.*.precio_promocional' => ['required', 'integer', 'min:1'],
            'items.*.fecha_inicio' => ['required', 'date'],
            'items.*.fecha_fin' => ['required', 'date', 'after_or_equal:items.*.fecha_inicio'],
            'items.*.activo' => ['sometimes', 'boolean'],
        ]);

        $reemplazar = (bool)($validated['reemplazar_conflictos'] ?? false);
        $items = $validated['items'];

        // Verificar que no haya combinaciones duplicadas ni conflicto General vs Tamaño dentro del mismo lote
        $seen = [];
        $batchScopesByProduct = [];
        foreach ($items as $idx => $item) {
            $prodId = (int)$item['id_producto'];
            $tamScope = isset($item['id_tamaño']) ? (string)(int)$item['id_tamaño'] : 'all';
            $key = $prodId . ':' . $tamScope;
            if (isset($seen[$key])) {
                return response()->json([
                    'message' => "Hay combinaciones de producto y tamaño repetidas en la fila #" . ($idx + 1) . " del lote."
                ], 422);
            }
            $seen[$key] = true;

            $prevScopes = $batchScopesByProduct[$prodId] ?? [];
            if ($tamScope === 'all' && !empty($prevScopes)) {
                return response()->json([
                    'message' => "No puedes mezclar \"Todos los tamaños\" con tamaños específicos del mismo producto en la fila #" . ($idx + 1) . "."
                ], 422);
            }
            if ($tamScope !== 'all' && in_array('all', $prevScopes, true)) {
                return response()->json([
                    'message' => "No puedes mezclar \"Todos los tamaños\" con tamaños específicos del mismo producto en la fila #" . ($idx + 1) . "."
                ], 422);
            }
            $batchScopesByProduct[$prodId][] = $tamScope;
        }

        foreach ($items as $item) {
            $errorMsg = $this->validarReglasNegocioPromocion($item, null, !$reemplazar);
            if ($errorMsg !== null) {
                return response()->json(['message' => $errorMsg], 422);
            }
        }

        $created = DB::transaction(function () use ($items, $reemplazar) {
            $results = [];

            foreach ($items as $item) {
                $isActive = array_key_exists('activo', $item) ? (bool)$item['activo'] : true;

                if ($reemplazar && $isActive) {
                    $tamId = isset($item['id_tamaño']) ? (int)$item['id_tamaño'] : null;
                    Promocion::where('id_producto', (int)$item['id_producto'])
                        ->when(
                            $tamId !== null,
                            fn ($q) => $q->where(fn ($sub) => $sub->where('id_tamaño', $tamId)->orWhereNull('id_tamaño')),
                            fn ($q) => $q
                        )
                        ->where('activo', true)
                        ->whereDate('fecha_inicio', '<=', $item['fecha_fin'])
                        ->whereDate('fecha_fin', '>=', $item['fecha_inicio'])
                        ->update(['activo' => false]);
                }

                $promo = Promocion::create([
                    'id_producto' => (int)$item['id_producto'],
                    'id_tamaño' => isset($item['id_tamaño']) ? (int)$item['id_tamaño'] : null,
                    'titulo' => trim((string)$item['titulo']),
                    'descripcion' => isset($item['descripcion']) && trim((string)$item['descripcion']) !== '' ? trim((string)$item['descripcion']) : null,
                    'precio_promocional' => (int)$item['precio_promocional'],
                    'fecha_inicio' => $item['fecha_inicio'],
                    'fecha_fin' => $item['fecha_fin'],
                    'activo' => $isActive,
                ])->load(['producto:id_producto,nombre', 'tamano:id_tamaño,nombre']);

                $results[] = $promo;
            }

            HistorialMovimiento::registrar(
                'oferta',
                'crear',
                'Creación múltiple de promociones',
                count($results) . ' promociones',
                'Se registraron ' . count($results) . ' promociones en lote.'
            );

            return $results;
        });

        CatalogCache::invalidate();

        return response()->json([
            'message' => count($created) . ' promociones creadas correctamente.',
            'items' => $created,
        ], 201);
    }

    public function update(Request $request, Promocion $promocion)
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_tamaño' => ['nullable', 'integer', 'exists:tamaños,id_tamaño'],
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_promocional' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $errorMsg = $this->validarReglasNegocioPromocion($data, $promocion->id_promocion);
        if ($errorMsg !== null) {
            return response()->json(['message' => $errorMsg], 422);
        }

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

    protected function validarReglasNegocioPromocion(array $data, ?int $ignoreId = null, bool $checkOverlap = true): ?string
    {
        $producto = Producto::with('tamaños')->find((int)$data['id_producto']);
        if (!$producto) {
            return 'Producto no encontrado.';
        }

        $tamId = isset($data['id_tamaño']) ? (int)$data['id_tamaño'] : null;
        $tamanos = $producto->tamaños ?? collect();

        $precioNormal = 0.0;
        if ($tamanos->isNotEmpty()) {
            if ($tamId !== null) {
                $tam = $tamanos->firstWhere('id_tamaño', $tamId);
                if (!$tam) {
                    return "El tamaño seleccionado no pertenece al producto \"{$producto->nombre}\".";
                }
                $precioNormal = (float)($tam->pivot->precio ?? 0);
            } else {
                $precioNormal = (float)($tamanos->min(fn ($t) => (float)($t->pivot->precio ?? 0)) ?? 0);
            }
        } else {
            $precioNormal = (float)($producto->precio_base ?? $producto->precio ?? 0);
        }

        if ($precioNormal > 0 && (float)$data['precio_promocional'] >= $precioNormal) {
            return "El precio promocional de \"{$producto->nombre}\" debe ser menor a su precio normal ($" . number_format($precioNormal, 0, ',', '.') . ').';
        }

        $isActive = !array_key_exists('activo', $data) || (bool)$data['activo'];
        if ($checkOverlap && $isActive) {
            $conflict = Promocion::where('id_producto', $producto->id_producto)
                ->when(
                    $tamId !== null,
                    fn ($q) => $q->where(fn ($sub) => $sub->where('id_tamaño', $tamId)->orWhereNull('id_tamaño')),
                    fn ($q) => $q
                )
                ->where('activo', true)
                ->when($ignoreId !== null, fn ($q) => $q->where('id_promocion', '!=', $ignoreId))
                ->whereDate('fecha_inicio', '<=', $data['fecha_fin'])
                ->whereDate('fecha_fin', '>=', $data['fecha_inicio'])
                ->first();

            if ($conflict) {
                $scopeLabel = $conflict->id_tamaño === null ? 'todos los tamaños' : 'ese tamaño';
                return "Ya existe una promoción activa superpuesta para \"{$producto->nombre}\" ({$scopeLabel}: \"{$conflict->titulo}\").";
            }
        }

        return null;
    }
}
