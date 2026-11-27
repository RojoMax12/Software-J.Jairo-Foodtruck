<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function index(Request $request)
    {
        $fecha = $request->query('fecha') ?? $request->query('fecha_turno');

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = (int) $request->query('per_page', 50);
            $page = (int) $request->query('page', 1);
            return response()->json($this->pedidoService->paginatePedidos($fecha, $perPage, $page));
        }

        $limit = (int) $request->query('limit', 300);
        return response()->json($this->pedidoService->getAllPedidos($fecha, $limit));
    }

    public function show(Request $request, $id)
    {
        $pedido = $this->pedidoService->getPedidoById($id);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        $user = $request->user();
        if ($user && !in_array((int)$user->id_rol, [1, 3], true)) {
            if ((int)$pedido->id_usuario !== (int)$user->id_usuario) {
                return response()->json(['message' => 'No autorizado para consultar este pedido.'], 403);
            }
        }

        return response()->json($pedido);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => 'nullable|integer|exists:usuarios,id_usuario',
            'id_estado_pago' => 'nullable|integer|in:1,2,3,4,5',
            'nombre_persona' => 'nullable|string|max:150',
            'persona_recibe' => 'nullable|string|max:150',
            'numero_telefono' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'metodo_pago' => 'nullable|string|max:60',
            'notas' => 'nullable|string|max:1000',
            'total' => 'nullable|numeric|min:0',
            'items' => 'required_without:detalles|array|min:1|max:100',
            'detalles' => 'required_without:items|array|min:1|max:100',
        ]);
        $user = $request->user();

        $pedidoRepo = app(\App\Repositories\PedidoRepository::class);
        $window = $pedidoRepo->getShiftWindow();

        // 1. Si el día de hoy está desactivado por administración, nadie puede ingresar pedidos
        if (!empty($window['is_day_off'])) {
            return response()->json([
                'success' => false,
                'is_closed' => true,
                'message' => 'No es posible ingresar pedidos: El día de hoy ha sido desactivado en la configuración de horarios (día de descanso programado).'
            ], 422);
        }

        // 2. Clientes regulares (Rol 2 o sin autenticación) solo pueden pedir durante el turno activo
        if (!$user || (int)$user->id_rol === 2) {
            if (empty($window['is_active'])) {
                $apertura = substr($window['hora_apertura'] ?? '19:00', 0, 5);
                $cierre = substr($window['hora_cierre'] ?? '00:30', 0, 5);
                return response()->json([
                    'success' => false,
                    'is_closed' => true,
                    'message' => "El Foodtruck se encuentra cerrado en este momento. Horario de atención: {$apertura} a {$cierre} hrs."
                ], 422);
            }
        }

        // Solo admin/trabajador pueden crear pedidos a nombre de otro usuario
        if (!$user || !in_array((int)$user->id_rol, [1, 3], true) || empty($data['id_usuario'])) {
            $data['id_usuario'] = $user?->id_usuario;
        }
        return response()->json($this->pedidoService->createPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_estado_pedido' => 'sometimes|integer|in:1,2,3,4,5',
            'id_estado_pago' => 'sometimes|integer|in:1,2,3,4,5',
            'nombre_persona' => 'sometimes|nullable|string|max:150',
            'numero_telefono' => 'sometimes|nullable|string|max:30',
            'metodo_pago' => 'sometimes|nullable|string|max:60',
            'notas' => 'sometimes|nullable|string|max:1000',
            'fecha_de_pago' => 'sometimes|nullable|date',
            'items' => 'sometimes|array|min:1|max:100',
        ]);
        return response()->json($this->pedidoService->updatePedido($id, $data));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede eliminar pedidos.'], 403);
        }

        $pedido = $this->pedidoService->getPedidoById($id);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        if ($pedido->inventario_descontado) {
            $this->pedidoService->revertirInventario($pedido->id_pedido);
        }

        $this->pedidoService->deletePedidoById($pedido->id_pedido);
        return response()->json(null, 204);
    }

    public function getMisPedidos(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        $limit = (int) $request->query('limit', 50);

        return response()->json($this->pedidoService->getPedidosByUsuarioId(
            $user->id_usuario,
            $fechaInicio,
            $fechaFin,
            $limit
        ));
    }

    public function getPedidosByUsuario(Request $request, $id)
    {
        $user = $request->user();

        // Si el usuario es un cliente (rol 2), solo puede ver sus propios pedidos
        $targetUserId = ($user && (int)$user->id_rol === 2) ? $user->id_usuario : (int)$id;

        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        $limit = (int) $request->query('limit', 50);

        return response()->json($this->pedidoService->getPedidosByUsuarioId(
            $targetUserId,
            $fechaInicio,
            $fechaFin,
            $limit
        ));
    }

    public function buscarPorComandaTurno(Request $request, $numeroComanda)
    {
        $fecha = $request->query('fecha');
        $cleanComanda = ltrim(trim($numeroComanda), '#');

        if (!is_numeric($cleanComanda) || (int)$cleanComanda <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'El número de comanda debe ser un número válido (ej: 1, 4, 12).'
            ], 400);
        }

        $resultado = $this->pedidoService->getPedidoByComandaTurno((int)$cleanComanda, $fecha);
        $pedido = $resultado['pedido'];
        $jornada = $resultado['jornada'];

        if (!$pedido) {
            $apertura = $jornada['hora_apertura'] ?? '19:00';
            $cierre = $jornada['hora_cierre'] ?? '00:30';
            return response()->json([
                'success' => false,
                'message' => "No encontramos el pedido #{$cleanComanda} en la jornada de atención actual ({$apertura} a {$cierre}). Verifica el número de tu comanda.",
                'jornada' => $jornada
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pedido,
            'jornada' => $jornada
        ]);
    }
}
