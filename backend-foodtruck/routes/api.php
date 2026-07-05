<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EstadoPedidoController;
use App\Http\Controllers\EstadoPagoController;
use App\Http\Controllers\IngredienteController;
use App\Http\Controllers\OfertaProductoController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoIngredienteController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\DetallePedidoIngredienteController;
use App\Http\Controllers\MovimientosController;
use App\Http\Controllers\HorarioAtencionController;
use App\Http\Controllers\ProductoTamañoController;
use App\Http\Controllers\TamañoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;

// ==========================================
// 1. AUTENTICACIÓN (LOGIN)
// ==========================================
Route::prefix('auth')->middleware('throttle:auth_limits')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ==========================================
// 2. ENTORNO PROTEGIDO - REQUIERE INICIO DE SESIÓN JWT
// ==========================================
// Las rutas están protegidas globalmente
Route::middleware('jwt.auth')->group(function () {
    
    // API Resources generarán automáticamente index, show, store, update, destroy
    Route::apiResource('cajas', CajaController::class);
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('estado_pedidos', EstadoPedidoController::class);
    Route::apiResource('ingredientes', IngredienteController::class);
    Route::apiResource('ofertas', OfertaController::class);
    Route::apiResource('oferta_productos', OfertaProductoController::class);
    Route::apiResource('pedidos', PedidoController::class);
    Route::apiResource('productos', ProductoController::class);
    Route::apiResource('producto_ingredientes', ProductoIngredienteController::class);
    Route::apiResource('roles', RolController::class);
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('ventas', VentaController::class);
    Route::apiResource('estado_pagos', EstadoPagoController::class);
    Route::apiResource('horario_atenciones', HorarioAtencionController::class);
    Route::apiResource('producto_tamaños', ProductoTamañoController::class);
    Route::apiResource('tamaños', TamañoController::class);
    Route::apiResource('movimientos', MovimientosController::class);
    Route::apiResource('detalle_pedidos', DetallePedidoController::class);
    Route::apiResource('detalle_pedido_ingredientes', DetallePedidoIngredienteController::class);
});