<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PurifyInput
{
    /**
     * Campos que no deben ser alterados para preservar contraseñas o datos binarios.
     */
    protected array $camposIgnorados = [
        'contrasena',
        'password',
        'password_confirmation',
        'contrasena_confirmation',
        'current_password',
        'new_password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Sanitizar query parameters (GET) contra XSS y SQLi
        if ($request->query->count() > 0) {
            $sanitizedQuery = $this->sanitizeArray($request->query->all());
            $request->query->replace($sanitizedQuery);
        }

        // 2. Si no es un método con payload (HEAD), continuamos
        if ($request->isMethod('HEAD')) {
            return $next($request);
        }

        // 3. Sanitizar cuerpo de la petición
        $input = $request->all();
        if (!empty($input)) {
            $sanitizedInput = $this->sanitizeArray($input);
            $request->merge($sanitizedInput);
        }

        return $next($request);
    }

    /**
     * Sanitiza de manera recursiva un array de entradas.
     */
    protected function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $this->camposIgnorados, true)) {
                // Para contraseñas solo removemos bytes nulos para prevenir ataques de truncamiento
                if (is_string($value)) {
                    $data[$key] = str_replace(chr(0), '', $value);
                }
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
        }

        return $data;
    }

    /**
     * Sanitiza una cadena de texto contra XSS, inyecciones de código y SQLi.
     */
    protected function sanitizeString(string $value): string
    {
        // 1. Eliminar bytes nulos (null byte injection)
        $clean = str_replace(chr(0), '', $value);

        // 2. Limpiar etiquetas HTML y scripts
        $clean = strip_tags($clean);

        // 3. Eliminar esquemas peligrosos como javascript:, vbscript:, data:
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
        $clean = preg_replace('/vbscript\s*:/i', '', $clean);
        $clean = preg_replace('/data\s*:\s*text\/html/i', '', $clean);

        // 4. Eliminar eventos de JavaScript inline (onerror=, onclick=, onload=, etc.)
        $clean = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = preg_replace('/on\w+\s*=\s*[^\s>]+/i', '', $clean);

        // 5. Neutralizar patrones de inyección SQL comunes en campos de texto libre
        $clean = preg_replace('/(--|\/\*|\*\/)/', '', $clean);

        // 6. Trimming de espacios en blanco
        return trim($clean);
    }
}