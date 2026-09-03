<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // 1. Validar que el local esté dentro de su turno y horario de atención
        $pedidoRepo = app(\App\Repositories\PedidoRepository::class);
        $window = $pedidoRepo->getShiftWindow();

        if (empty($window['is_active'])) {
            $apertura = substr($window['hora_apertura'] ?? '19:00', 0, 5);
            $cierre = substr($window['hora_cierre'] ?? '00:30', 0, 5);
            $isDayOff = !empty($window['is_day_off']);
            $msg = $isDayOff
                ? "El Foodtruck se encuentra cerrado hoy por ser día de descanso programado. ¡No es posible realizar pedidos hoy!"
                : "El Foodtruck se encuentra cerrado en este momento. Nuestro horario de atención es de {$apertura} a {$cierre} hrs. ¡Te esperamos en nuestro próximo turno!";

            return response()->json([
                'success' => false,
                'is_closed' => true,
                'message' => $msg,
                'horario' => [
                    'hora_apertura' => $apertura,
                    'hora_cierre' => $cierre,
                    'dia' => $window['dia'] ?? 'Hoy',
                    'es_dia_cerrado' => $isDayOff
                ]
            ], 422);
        }

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
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al registrar pedido público: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un inconveniente al procesar tu pedido. Por favor, inténtalo nuevamente o consulta con nuestro personal.'
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

            // Anonimizar teléfono para protección de datos personales
            if (!empty($pedido->numero_telefono)) {
                $pedido->numero_telefono = $this->maskPhone($pedido->numero_telefono);
            }

            return response()->json([
                'success' => true,
                'data' => $pedido
            ]);
        } catch (\Exception $e) {
            Log::error("Error al consultar pedido ID #{$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un inconveniente al consultar el pedido. Por favor, intenta más tarde.'
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

        // Anonimizar teléfono para protección de datos en pantallas públicas
        if (!empty($pedido->numero_telefono)) {
            $pedido->numero_telefono = $this->maskPhone($pedido->numero_telefono);
        }

        return response()->json([
            'success' => true,
            'data' => $pedido,
            'jornada' => $jornada
        ]);
    }

    private function maskPhone($phone): string
    {
        if (!$phone) return '';
        $clean = preg_replace('/\D/', '', $phone);
        if (strlen($clean) >= 8) {
            $last4 = substr($clean, -4);
            return '+56 9 **** ' . $last4;
        }
        return '****';
    }
}
