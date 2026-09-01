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
        return response()->json($this->pedidoService->getAllPedidos($fecha));
    }

    public function show($id)
    {
        return response()->json($this->pedidoService->getPedidoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        // Clientes regulares (Rol 2 o sin rol especial) solo pueden pedir durante el turno activo
        if (!$user || $user->id_rol === 2) {
            $pedidoRepo = app(\App\Repositories\PedidoRepository::class);
            $window = $pedidoRepo->getShiftWindow();
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

        if (empty($data['id_usuario']) && $user) {
            $data['id_usuario'] = $user->id_usuario;
        }
        return response()->json($this->pedidoService->createPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->pedidoService->updatePedido($id, $data));
    }

    public function destroy($id)
    {
        $this->pedidoService->deletePedidoById($id);
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
