<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Obtener el usuario autenticado por JWT
        $user = auth()->user();

        if (!$user || !in_array($user->id_rol, $roles)) {
            return response()->json(['message' => 'No tienes permisos para realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
