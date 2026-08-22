<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuthenticateJwt
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorized('Token no proporcionado.');
        }

        try {
            $claims = $this->jwtService->decode($token);
        } catch (Throwable) {
            return $this->unauthorized('Token inválido o expirado.');
        }

        if ($this->jwtService->isBlacklisted($claims->jti ?? null)) {
            return $this->unauthorized('Token revocado.');
        }

        $user = $this->resolveUserFromClaims($claims);

        if (! $user) {
            return $this->unauthorized('Usuario no encontrado.');
        }

        Auth::setUser($user);
        $request->setUserResolver(static fn () => $user);
        $request->attributes->set('jwt_claims', $claims);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 401);
    }

    private function resolveUserFromClaims(object $claims): ?\App\Models\Usuario
    {
        $userId = $claims->sub ?? null;

        if (! $userId) {
            return null;
        }

        return \App\Models\Usuario::find($userId);
    }
}