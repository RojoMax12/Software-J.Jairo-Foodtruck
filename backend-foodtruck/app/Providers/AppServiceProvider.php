<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Perfil para Autenticación (Previene fuerza bruta informando el tiempo de espera)
        RateLimiter::for('auth_limits', function (Request $request) {
            $path = $request->path();
            $isRegister = str_contains($path, 'register') || str_contains($path, 'distribuidores');
            $actionName = $isRegister ? 'registro de cuenta' : 'inicio de sesión';

            return Limit::perMinute(15)->by($request->ip())->response(function (Request $request, array $headers) use ($actionName) {
                $seconds = isset($headers['Retry-After']) ? (int)$headers['Retry-After'] : 60;
                $tiempoTexto = $seconds < 60 
                    ? "{$seconds} segundo" . ($seconds > 1 ? 's' : '')
                    : ceil($seconds / 60) . " minuto" . (ceil($seconds / 60) > 1 ? 's' : '');

                return response()->json([
                    'message' => "Has realizado demasiados intentos de {$actionName}. Por favor, espera {$tiempoTexto} antes de volver a intentarlo.",
                    'retry_after' => $seconds,
                    'retry_after_seconds' => $seconds,
                ], 429, $headers);
            });
        });

        RateLimiter::for('api_escritura', function (Request $request) {
            return Limit::perMinute(40)->by($request->ip())->response(function (Request $request, array $headers) {
                $seconds = isset($headers['Retry-After']) ? (int)$headers['Retry-After'] : 60;
                return response()->json([
                    'message' => "Límite de peticiones de escritura excedido. Por favor, espera {$seconds} segundos.",
                    'retry_after' => $seconds,
                ], 429, $headers);
            });
        });

        RateLimiter::for('api_lectura', function (Request $request) {
            return Limit::perMinute(150)->by($request->ip())->response(function (Request $request, array $headers) {
                $seconds = isset($headers['Retry-After']) ? (int)$headers['Retry-After'] : 60;
                return response()->json([
                    'message' => "Límite de peticiones de lectura excedido. Por favor, espera {$seconds} segundos.",
                    'retry_after' => $seconds,
                ], 429, $headers);
            });
        });

        
    }
}
