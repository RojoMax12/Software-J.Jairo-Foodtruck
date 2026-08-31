<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Campos sensibles que deben ser enmascarados de forma estricta
     * para proteger la privacidad del titular (Ley N° 21.719 en Chile).
     */
    protected array $maskedFields = [
        'contrasena',
        'password',
        'password_confirmation',
        'contrasena_confirmation',
        'token',
        'access_token',
        'authorization',
        'tarjeta',
        'numero_tarjeta',
        'cvv',
        'cvc',
        'pin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // Registrar auditoría para todas las operaciones mutantes o rutas de autenticación y errores
        if ($this->shouldLog($request, $response)) {
            $this->logAuditEvent($request, $response, $durationMs);
        }

        return $response;
    }

    /**
     * Determina si la petición actual debe registrarse en la pista de auditoría.
     */
    protected function shouldLog(Request $request, Response $response): bool
    {
        // 1. Siempre registrar peticiones que modifiquen datos (POST, PUT, PATCH, DELETE)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        // 2. Registrar accesos a autenticación, cierres de caja o usuarios
        $path = $request->path();
        if (str_contains($path, 'auth') || str_contains($path, 'caja') || str_contains($path, 'usuarios')) {
            return true;
        }

        // 3. Registrar cualquier error 4xx o 5xx (intentos no autorizados, ataques, etc.)
        if ($response->getStatusCode() >= 400) {
            return true;
        }

        return false;
    }

    /**
     * Escribe el registro estructurado de auditoría en el log del servidor.
     */
    protected function logAuditEvent(Request $request, Response $response, float $durationMs): void
    {
        $user = $request->user();
        $userId = $user?->id_usuario ?? $user?->id ?? null;
        $userEmail = $user?->correo ?? $user?->email ?? null;

        $maskedPayload = $this->maskSensitiveData($request->all());

        $auditRecord = [
            'event' => $this->resolveEventName($request, $response),
            'timestamp' => now()->toIso8601String(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'user' => [
                'id' => $userId,
                'email' => $userEmail,
                'role' => $user?->id_rol ?? null,
                'authenticated' => !is_null($user),
            ],
            'payload' => $maskedPayload,
            'user_agent' => substr((string) $request->userAgent(), 0, 150),
        ];

        try {
            Log::channel('audit')->info('AUDIT_EVENT', $auditRecord);
        } catch (\Throwable $e) {
            Log::info('AUDIT_EVENT_FALLBACK', $auditRecord);
        }
    }

    /**
     * Clasifica el tipo de evento para análisis forense y trazabilidad legal.
     */
    protected function resolveEventName(Request $request, Response $response): string
    {
        $path = strtolower($request->path());
        $method = $request->method();
        $status = $response->getStatusCode();

        if (str_contains($path, 'auth/login')) {
            return $status === 200 ? 'AUTH_LOGIN_SUCCESS' : 'AUTH_LOGIN_FAILED';
        }
        if (str_contains($path, 'auth/logout')) return 'AUTH_LOGOUT';
        if (str_contains($path, 'auth/forgot-password')) return 'AUTH_FORGOT_PASSWORD_REQUEST';
        if (str_contains($path, 'auth/reset-password')) return 'AUTH_RESET_PASSWORD_EXECUTE';

        if (str_contains($path, 'pedidos')) {
            if ($method === 'POST') return 'ORDER_CREATED';
            if (in_array($method, ['PUT', 'PATCH'])) return 'ORDER_UPDATED';
            if ($method === 'DELETE') return 'ORDER_CANCELLED';
        }

        if (str_contains($path, 'productos')) {
            if ($method === 'POST') return 'PRODUCT_CREATED';
            if (in_array($method, ['PUT', 'PATCH'])) return 'PRODUCT_UPDATED';
            if ($method === 'DELETE') return 'PRODUCT_DELETED';
        }

        if (str_contains($path, 'categorias')) {
            if ($method === 'POST') return 'CATEGORY_CREATED';
            if (in_array($method, ['PUT', 'PATCH'])) return 'CATEGORY_UPDATED';
            if ($method === 'DELETE') return 'CATEGORY_DELETED';
        }

        if (str_contains($path, 'tamaños') || str_contains($path, 'tamanos')) {
            if ($method === 'POST') return 'SIZE_CREATED';
            if (in_array($method, ['PUT', 'PATCH'])) return 'SIZE_UPDATED';
            if ($method === 'DELETE') return 'SIZE_DELETED';
        }

        if (str_contains($path, 'caja')) return 'CASH_FLOW_MUTATION';
        if (str_contains($path, 'ingredientes') || str_contains($path, 'stock')) return 'INVENTORY_MUTATION';
        if (str_contains($path, 'usuarios')) return 'USER_MANAGEMENT_MUTATION';

        return "HTTP_{$method}_ACTION";
    }

    /**
     * Enmascara recursivamente contraseñas y datos sensibles.
     */
    protected function maskSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);
            if (in_array($lowerKey, $this->maskedFields, true)) {
                $data[$key] = '***CONFIDENCIAL_LEY_21719***';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            }
        }

        return $data;
    }
}

