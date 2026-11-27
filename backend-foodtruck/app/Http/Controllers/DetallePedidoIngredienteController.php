<?php

namespace App\Http\Controllers;

use App\Models\Detalle_pedido;
use App\Models\Detalle_pedido_Ingrediente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\DetallePedidoIngredienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallePedidoIngredienteController extends Controller
{
    protected $detallePedidoIngredienteService;

    public function __construct(DetallePedidoIngredienteService $detallePedidoIngredienteService)
    {
        $this->detallePedidoIngredienteService = $detallePedidoIngredienteService;
    }

    public function index()
    {
        return response()->json($this->detallePedidoIngredienteService->getAllDetallePedidoIngredientes());
    }

    public function show($id)
    {
        $detalleIng = $this->detallePedidoIngredienteService->getDetallePedidoIngredienteById($id);
        if (!$detalleIng) {
            return response()->json(['message' => 'Modificación de ingrediente no encontrada'], 404);
        }
        return response()->json($detalleIng);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_detalle_pedido' => 'required|integer|exists:detalle_pedido,id_detalle_pedido',
            'id_ingrediente' => 'required|integer|exists:ingredientes,id_ingrediente',
            'tipo_modificacion' => 'required|string|max:40',
        ]);

        $detallePedidoIngrediente = DB::transaction(function () use ($data) {
            $detalle = Detalle_pedido::with('producto.ingredientes')->findOrFail((int)$data['id_detalle_pedido']);
            $data['precio_aplicado'] = $this->resolverPrecioModificacion(
                $detalle,
                (int)$data['id_ingrediente'],
                (string)$data['tipo_modificacion']
            );

            $created = $this->detallePedidoIngredienteService->createDetallePedidoIngrediente($data);
            $this->recalcularDetalleYPedido((int)$data['id_detalle_pedido']);
            return $created;
        });

        return response()->json($detallePedidoIngrediente, 201);
    }

    public function update(Request $request, $id)
    {
        $existente = $this->detallePedidoIngredienteService->getDetallePedidoIngredienteById($id);
        if (!$existente) {
            return response()->json(['message' => 'Modificación de ingrediente no encontrada'], 404);
        }

        $data = $request->validate([
            'tipo_modificacion' => 'sometimes|string|max:40',
        ]);

        $detallePedidoIngrediente = DB::transaction(function () use ($id, $data, $existente) {
            $detalle = Detalle_pedido::with('producto.ingredientes')->findOrFail((int)$existente->id_detalle_pedido);
            $tipo = (string)($data['tipo_modificacion'] ?? $existente->tipo_modificacion);
            $data['precio_aplicado'] = $this->resolverPrecioModificacion(
                $detalle,
                (int)$existente->id_ingrediente,
                $tipo
            );

            $updated = $this->detallePedidoIngredienteService->updateDetallePedidoIngrediente($id, $data);
            $this->recalcularDetalleYPedido((int)$existente->id_detalle_pedido);
            return $updated;
        });

        return response()->json($detallePedidoIngrediente);
    }

    public function destroy($id)
    {
        $existente = $this->detallePedidoIngredienteService->getDetallePedidoIngredienteById($id);
        if (!$existente) {
            return response()->json(['message' => 'Modificación de ingrediente no encontrada'], 404);
        }

        DB::transaction(function () use ($id, $existente) {
            $this->detallePedidoIngredienteService->deleteDetallePedidoIngredienteById($id);
            $this->recalcularDetalleYPedido((int)$existente->id_detalle_pedido);
        });

        return response()->json(null, 204);
    }

    protected function resolverPrecioModificacion(Detalle_pedido $detalle, int $idIngrediente, string $tipo): int
    {
        $lower = mb_strtolower(trim($tipo), 'UTF-8');
        if (str_contains($lower, 'exclu') || str_contains($lower, 'sin') || str_contains($lower, 'quit')) {
            return 0;
        }

        $producto = $detalle->producto ?? Producto::with('ingredientes')->find($detalle->id_producto);
        if (!$producto) {
            return 0;
        }

        $pi = ($producto->ingredientes ?? collect())->first(function ($item) use ($idIngrediente, $detalle) {
            if ((int)$item->id_ingrediente !== $idIngrediente) {
                return false;
            }
            return $detalle->id_tamaño
                ? ($item->id_tamaño === null || (int)$item->id_tamaño === (int)$detalle->id_tamaño)
                : ($item->id_tamaño === null);
        }) ?? ($producto->ingredientes ?? collect())->firstWhere('id_ingrediente', $idIngrediente);

        if ($pi && $pi->precio_extra !== null) {
            return (int)round((float)$pi->precio_extra);
        }

        return (int)round((float)($producto->precio_ingrediente_extra ?? 0));
    }

    protected function recalcularDetalleYPedido(int $idDetallePedido): void
    {
        $detalle = Detalle_pedido::with(['producto.tamaños', 'producto.promocionesActivas', 'ingredientes'])
            ->find($idDetallePedido);

        if (!$detalle || !$detalle->producto) {
            return;
        }

        $producto = $detalle->producto;
        $tamanos = $producto->tamaños ?? collect();
        $idTamaño = $detalle->id_tamaño ? (int)$detalle->id_tamaño : null;

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

        $agregadosMods = ($detalle->ingredientes ?? collect())->filter(function ($mod) {
            $tipo = mb_strtolower(trim((string)($mod->tipo_modificacion ?? '')), 'UTF-8');
            return !str_contains($tipo, 'exclu') && !str_contains($tipo, 'sin') && !str_contains($tipo, 'quit');
        });

        $extrasSum = 0.0;
        if ($agregadosMods->isNotEmpty()) {
            $producto->loadMissing(['categoria', 'ingredientes.ingrediente']);
            $rawAgregados = $agregadosMods->map(fn ($mod) => [
                'id_ingrediente' => $mod->id_ingrediente,
                'nombre' => $mod->ingrediente->nombre ?? '',
            ])->values()->all();

            $pedidoRepo = app(\App\Repositories\PedidoRepository::class);
            $extrasCalc = $pedidoRepo->calcularCostoExtras($producto, $rawAgregados, $idTamaño);
            $extrasSum = (float)$extrasCalc['total'];
            $preciosPorId = $extrasCalc['precios_por_id'] ?? [];

            foreach ($agregadosMods as $mod) {
                if ($mod->id_ingrediente && array_key_exists((int)$mod->id_ingrediente, $preciosPorId)) {
                    $nuevoPrecio = (int)round((float)$preciosPorId[(int)$mod->id_ingrediente]);
                    if ((int)$mod->precio_aplicado !== $nuevoPrecio) {
                        $mod->precio_aplicado = $nuevoPrecio;
                        $mod->save();
                    }
                }
            }
        }

        $detalle->precio_unitario = (int)round($precioBase + $extrasSum);
        $detalle->save();

        $pedido = Pedido::find($detalle->id_pedido);
        if ($pedido) {
            $pedido->total = (int)Detalle_pedido::where('id_pedido', $pedido->id_pedido)
                ->get()
                ->sum(fn ($d) => (int)$d->cantidad * (int)$d->precio_unitario);
            $pedido->save();
        }
    }

    public function createDetallePedidoIngrediente(Request $request)
    {
        return $this->store($request);
    }

    public function getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido)
    {
        $detallePedidoIngredientes = $this->detallePedidoIngredienteService->getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido);
        return response()->json($detallePedidoIngredientes);
    }
}
