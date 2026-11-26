<?php

namespace App\Http\Controllers;

use App\Models\Detalle_pedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\DetallePedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallePedidoController extends Controller
{
    protected $detallePedidoService;

    public function __construct(DetallePedidoService $detallePedidoService)
    {
        $this->detallePedidoService = $detallePedidoService;
    }

    public function index(Request $request)
    {
        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = max(1, min(200, (int) $request->query('per_page', 50)));
            return response()->json(
                Detalle_pedido::with(['producto', 'tamano', 'ingredientes.ingrediente'])
                    ->orderBy('id_detalle_pedido', 'desc')
                    ->paginate($perPage)
            );
        }
        return response()->json($this->detallePedidoService->getAllDetallePedidos());
    }

    public function show($id)
    {
        $detalle = $this->detallePedidoService->getDetallePedidoById($id);
        if (!$detalle) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }
        return response()->json($detalle);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'cantidad' => 'required|integer|min:1|max:100',
        ]);

        $detallePedido = DB::transaction(function () use ($data) {
            $data['precio_unitario'] = $this->resolverPrecioCatalogo(
                (int)$data['id_producto'],
                isset($data['id_tamaño']) ? (int)$data['id_tamaño'] : null
            );

            $created = $this->detallePedidoService->createDetallePedido($data);
            $this->recalcularTotalPedido((int)$data['id_pedido']);
            return $created;
        });

        return response()->json($detallePedido, 201);
    }

    public function update(Request $request, $id)
    {
        $existente = $this->detallePedidoService->getDetallePedidoById($id);
        if (!$existente) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }

        $data = $request->validate([
            'id_producto' => 'sometimes|integer|exists:productos,id_producto',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'cantidad' => 'sometimes|integer|min:1|max:100',
        ]);

        $detallePedido = DB::transaction(function () use ($id, $data, $existente) {
            $prodId = (int)($data['id_producto'] ?? $existente->id_producto);
            $tamId = array_key_exists('id_tamaño', $data)
                ? ($data['id_tamaño'] ? (int)$data['id_tamaño'] : null)
                : ($existente->id_tamaño ? (int)$existente->id_tamaño : null);

            $data['precio_unitario'] = $this->resolverPrecioCatalogo($prodId, $tamId);
            $updated = $this->detallePedidoService->updateDetallePedido($id, $data);
            $this->recalcularTotalPedido((int)$existente->id_pedido);
            return $updated;
        });

        return response()->json($detallePedido);
    }

    public function destroy($id)
    {
        $existente = $this->detallePedidoService->getDetallePedidoById($id);
        if (!$existente) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }

        DB::transaction(function () use ($id, $existente) {
            $this->detallePedidoService->deleteDetallePedidoById($id);
            $this->recalcularTotalPedido((int)$existente->id_pedido);
        });

        return response()->json(null, 204);
    }

    protected function resolverPrecioCatalogo(int $idProducto, ?int $idTamaño): int
    {
        $producto = Producto::with(['tamaños', 'promocionesActivas'])->findOrFail($idProducto);
        $tamanos = $producto->tamaños ?? collect();

        $precioBase = 0.0;
        if ($tamanos->isNotEmpty()) {
            $tamRow = $idTamaño
                ? ($tamanos->firstWhere('id_tamaño', $idTamaño) ?? $tamanos->first())
                : $tamanos->sortBy('pivot.precio')->first();
            $precioBase = (float)($tamRow->pivot->precio ?? 0);
        } else {
            $precioBase = (float)($producto->precio ?? $producto->precio_base ?? 0);
        }

        $todayStr = now()->format('Y-m-d');
        $vigentes = ($producto->promocionesActivas ?? collect())->filter(function ($promo) use ($todayStr) {
            if (empty($promo->activo)) {
                return false;
            }
            $inicio = $promo->fecha_inicio ? $promo->fecha_inicio->format('Y-m-d') : '';
            $fin = $promo->fecha_fin ? $promo->fecha_fin->format('Y-m-d') : '';
            return (!$inicio || $inicio <= $todayStr) && (!$fin || $fin >= $todayStr);
        });

        $promo = $vigentes->first(fn ($p) => $idTamaño && $p->id_tamaño !== null && (int)$p->id_tamaño === $idTamaño)
            ?? $vigentes->first(fn ($p) => $p->id_tamaño === null);

        if ($promo && (float)$promo->precio_promocional > 0) {
            $promoPrice = (float)$promo->precio_promocional;
            $precioBase = $precioBase > 0 ? min($promoPrice, $precioBase) : $promoPrice;
        }

        return (int)round($precioBase);
    }

    protected function recalcularTotalPedido(int $idPedido): void
    {
        $pedido = Pedido::find($idPedido);
        if (!$pedido) {
            return;
        }
        $sum = Detalle_pedido::where('id_pedido', $idPedido)
            ->get()
            ->sum(fn ($d) => (int)$d->cantidad * (int)$d->precio_unitario);
        $pedido->total = (int)$sum;
        $pedido->save();
    }

    public function createDetallePedido(Request $request)
    {
        return $this->store($request);
    }

    public function getDetallePedidosByPedidoId($id_pedido)
    {
        $detallePedidos = $this->detallePedidoService->getDetallePedidosByPedidoId($id_pedido);
        return response()->json($detallePedidos);
    }
}
