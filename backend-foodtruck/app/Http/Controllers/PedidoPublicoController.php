<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
use Illuminate\Http\Request;

class PedidoPublicoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    /**
     * Crear pedido público (desde el checkout sin login o con login de cliente)
     */
    public function storePublico(Request $request)
    {
        $data = $request->all();
        $user = $request->user();
        if (empty($data['id_usuario']) && $user) {
            $data['id_usuario'] = $user->id_usuario;
        }

        try {
            $pedido = $this->pedidoService->createPedido($data);
            return response()->json([
                'success' => true,
                'message' => 'Pedido registrado exitosamente.',
                'data' => $pedido
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar pedido por ID de forma pública (para seguimiento)
     */
    public function buscarPorId($id)
    {
        try {
            $pedido = $this->pedidoService->getPedidoById($id);
            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => "No encontramos el pedido con ID #{$id}."
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $pedido
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar pedido por número de comanda del turno actual
     */
    public function buscarPorComanda(Request $request, $numeroComanda)
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
                'message' => "No encontramos el pedido #{$cleanComanda} en la jornada de atención ({$apertura} a {$cierre}). Verifica el número de tu comanda.",
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

