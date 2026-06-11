<?php

namespace App\Http\Middleware;

use App\Models\Usuario_dicreme;
use App\Models\Usuario_distribuidores;
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

    private function resolveUserFromClaims(object $claims): Usuario_dicreme|Usuario_distribuidores|null
    {
        $userId = $claims->sub ?? null;
        $userType = strtolower((string) ($claims->user_type ?? ''));

        if (! $userId || $userType === '') {
            return null;
        }

        return match (true) {
            str_contains($userType, 'dicreme') => Usuario_dicreme::find($userId),
            str_contains($userType, 'distribuidor') => Usuario_distribuidores::find($userId),
            default => null,
        };
    }
}