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
        // 1. Perfil ultra estricto para Autenticación (Previene DDoS por fuerza bruta al login)
        RateLimiter::for('auth_limits', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json(['message' => 'Demasiados intentos de inicio de sesión. Por favor, intenta más tarde.'], 429);
            });
        });


        RateLimiter::for('api_escritura', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip())->response(function () {
                return response()->json(['message' => 'Límite de peticiones de escritura excedido. Por favor, intenta más tarde.'], 429);
            });
        });

        RateLimiter::for('api_lectura', function (Request $request) {
            return Limit::perMinute(90)->by($request->ip())->response(function () {
                return response()->json(['message' => 'Límite de peticiones de lectura excedido. Por favor, intenta más tarde.'], 429);
            });
        });

        
    }
}
