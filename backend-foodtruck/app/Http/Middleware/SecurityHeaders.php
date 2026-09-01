<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Aplica encabezados HTTP de seguridad y ciberseguridad a todas las respuestas de la API
     * conforme a las directrices de la Ley N° 21.719 en Chile.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Prevenir que el navegador interprete archivos con tipos MIME incorrectos
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Prevenir ataques de Clickjacking restringiendo el framing al mismo origen
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Activar el filtro XSS incorporado en navegadores compatibles
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 4. Controlar la información del referente enviada en peticiones
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Restringir el acceso a APIs de hardware del dispositivo del cliente
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), payment=()');

        // 6. Política de seguridad de contenido balanceada para APIs REST
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        return $response;
    }
}

