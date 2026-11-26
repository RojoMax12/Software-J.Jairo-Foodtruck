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
        if ($request->query->count() > 0) {
            $sanitizedQuery = $this->sanitizeArray($request->query->all());
            $request->query->replace($sanitizedQuery);
        }

        if ($request->isMethod('HEAD')) {
            return $next($request);
        }

        $input = $request->all();
        if (!empty($input)) {
            $sanitizedInput = $this->sanitizeArray($input);
            $request->merge($sanitizedInput);
        }

        return $next($request);
    }

    protected function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $this->camposIgnorados, true)) {
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
     * Sanitiza cadenas de entrada preservando texto legítimo (guiones, puntuaciones)
     * y bloqueando null-bytes, etiquetas ejecutables y esquemas activos.
     */
    protected function sanitizeString(string $value): string
    {
        $clean = str_replace(chr(0), '', $value);
        $clean = preg_replace('#<script\b[^>]*>[\s\S]*?</script>#i', '', $clean);
        $clean = preg_replace('#</?(?:script|iframe|object|embed|svg)\b[^>]*>#i', '', (string) $clean);
        $clean = preg_replace('#\bon[a-z]+\s*=\s*(?:"[^"]*"|\x27[^\x27]*\x27|[^\s>]+)#i', '', (string) $clean);
        $clean = preg_replace('#javascript\s*:#i', '', (string) $clean);
        $clean = preg_replace('#vbscript\s*:#i', '', (string) $clean);
        $clean = preg_replace('#data\s*:\s*text/html#i', '', (string) $clean);

        return trim((string) $clean);
    }
}
