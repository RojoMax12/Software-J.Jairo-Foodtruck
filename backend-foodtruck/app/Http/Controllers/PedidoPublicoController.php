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

        $data = $request->validate([
            'nombre_persona' => 'required_without:persona_recibe|string|max:150',
            'persona_recibe' => 'required_without:nombre_persona|string|max:150',
            'numero_telefono' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'metodo_pago' => 'nullable|string|max:60',
            'notas' => 'nullable|string|max:1000',
            'total' => 'nullable|numeric|min:0',
            'items' => 'required_without:detalles|array|min:1|max:100',
            'detalles' => 'required_without:items|array|min:1|max:100',
        ]);
        $user = $this->resolverUsuarioOpcional($request);
        // El pedido siempre se asocia al usuario autenticado, nunca a un id enviado por el cliente
        $data['id_usuario'] = $user?->id_usuario;

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
     * Buscar pedido de forma pública únicamente mediante su token criptográfico de seguimiento.
     * Previene enumeración secuencial (IDOR) por ID autoincremental.
     */
    public function buscarPorId($id)
    {
        $token = trim((string) $id);
        if (strlen($token) < 12 || ctype_digit($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Para consultar el pedido ingresa el número de comanda del turno o usa tu enlace de seguimiento.'
            ], 404);
        }

        try {
            $pedido = \App\Models\Pedido::with([
                'detalles.producto',
                'detalles.tamano',
                'detalles.tamaño',
                'detalles.ingredientes.ingrediente',
                'estadoPedido',
                'estadoPago',
            ])->where('tracking_token', $token)->first();

            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'No encontramos un pedido asociado a ese código de seguimiento.'
                ], 404);
            }

            if (!empty($pedido->numero_telefono)) {
                $pedido->numero_telefono = $this->maskPhone($pedido->numero_telefono);
            }

            return response()->json([
                'success' => true,
                'data' => $this->sanitizarPedidoPublico($pedido)
            ]);
        } catch (\Exception $e) {
            Log::error("Error al consultar pedido por token: " . $e->getMessage());
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

        if (!empty($pedido->numero_telefono)) {
            $pedido->numero_telefono = $this->maskPhone($pedido->numero_telefono);
        }

        return response()->json([
            'success' => true,
            'data' => $this->sanitizarPedidoPublico($pedido),
            'jornada' => $jornada
        ]);
    }

    private function resolverUsuarioOpcional(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            $jwt = app(\App\Services\JwtService::class);
            $claims = $jwt->decode($token);
            if ($jwt->isBlacklisted($claims->jti ?? null)) {
                return null;
            }
            $user = \App\Models\Usuario::find($claims->sub ?? null);
            if (!$user || (isset($user->estado) && !$user->estado)) {
                return null;
            }
            if (!hash_equals($jwt->passwordVersion($user), (string) ($claims->pv ?? ''))) {
                return null;
            }
            return $user;
        } catch (\Throwable) {
            return null;
        }
    }

    private function sanitizarPedidoPublico($pedido)
    {
        $pedido->unsetRelation('usuario');
        return $pedido->makeHidden(['id_usuario', 'usuario']);
    }

    private function maskPhone($phone): string
    {
        if (!$phone) return '';
        $clean = preg_replace('/\D/', '', $phone);
        if (strlen($clean) >= 8) {
            $last4 = substr($clean, -4);
            return '+56 **** ' . $last4;
        }
        return '****';
    }
}
