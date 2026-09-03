<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\DetallePedidoIngredienteController;
use App\Http\Controllers\EstadoPagoController;
use App\Http\Controllers\EstadoPedidoController;
use App\Http\Controllers\HistorialMovimientoController;
use App\Http\Controllers\HorarioAtencionController;
use App\Http\Controllers\IngredienteController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MovimientosController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\OfertaProductoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoPublicoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoIngredienteController;
use App\Http\Controllers\ProductoTamañoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TamañoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. AUTENTICACIÓN (LOGIN & REGISTRO CON RATE LIMITING)
// ==========================================
Route::prefix('auth')->middleware('throttle:auth_limits')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/profile', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Alias protegido con rate limit para distribuidores
Route::post('/usuarios_distribuidores', [AuthController::class, 'register'])->middleware('throttle:auth_limits');

// ==========================================
// 2. RUTAS PÚBLICAS PARA MENÚ QR Y PEDIDOS DE CLIENTES
// ==========================================
Route::prefix('public')->group(function () {
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::post('/pedidos', [PedidoPublicoController::class, 'storePublico']);
    Route::get('/pedidos/comanda/{numeroComanda}', [PedidoPublicoController::class, 'buscarPorComanda']);
    Route::get('/pedidos/{id}', [PedidoPublicoController::class, 'buscarPorId']);
    Route::get('/marketing', [MarketingController::class, 'index']);
    Route::get('/horarios/turno-actual', [HorarioAtencionController::class, 'getTurnoActual']);
});
Route::get('/horarios/turno-actual', [HorarioAtencionController::class, 'getTurnoActual']);


// ==========================================
// 3. RUTAS DE CONSULTA Y CREACIÓN DE PEDIDOS PARA CLIENTES Y DISTRIBUIDORES
// ==========================================
Route::middleware('jwt.auth')->group(function () {
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/pedidos/mis-pedidos', [PedidoController::class, 'getMisPedidos']);
    Route::get('/pedidos/{id}/usuario_distribuidor', [PedidoController::class, 'getPedidosByUsuario']);
    Route::get('/pedidos/usuario/{id}', [PedidoController::class, 'getPedidosByUsuario']);
});


// ==========================================
// 4. ENTORNO PROTEGIDO - CONTROL DE ACCESO POR ROLES (JWT)
// ==========================================
Route::middleware('jwt.auth')->group(function () {

    // A. EXCLUSIVO ADMINISTRADOR (Rol 1 = Admin)
    Route::middleware('role:1')->group(function () {
        Route::apiResource('cajas', CajaController::class);
        Route::apiResource('ventas', VentaController::class);
        Route::apiResource('usuarios', UsuarioController::class);
        Route::get('usuarios-administrativos', [UsuarioController::class, 'administrativos']);
        Route::apiResource('roles', RolController::class);
        Route::apiResource('horario_atenciones', HorarioAtencionController::class);
        Route::apiResource('movimientos', MovimientosController::class);
        Route::delete('historial_movimientos-clear', [HistorialMovimientoController::class, 'clear']);
    });

    // B. ADMINISTRADORES Y TRABAJADORES (Rol 1 = Admin, Rol 3 = Trabajador/Cocina)
    Route::middleware('role:1,3')->group(function () {
        Route::apiResource('categorias', CategoriaController::class);
        Route::apiResource('tamaños', TamañoController::class);
        Route::post('productos/{id}/imagen', [ProductoController::class, 'uploadImage']);
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('producto_ingredientes', ProductoIngredienteController::class);
        Route::apiResource('producto_tamaños', ProductoTamañoController::class);
        Route::apiResource('ofertas', OfertaController::class);
        Route::apiResource('oferta_productos', OfertaProductoController::class);
        Route::apiResource('ingredientes', IngredienteController::class);
        Route::apiResource('pedidos', PedidoController::class)->except(['show', 'store']);
        Route::apiResource('detalle_pedidos', DetallePedidoController::class);
        Route::apiResource('detalle_pedido_ingredientes', DetallePedidoIngredienteController::class);
        Route::apiResource('estado_pedidos', EstadoPedidoController::class);
        Route::apiResource('estado_pagos', EstadoPagoController::class);
        Route::apiResource('historial_movimientos', HistorialMovimientoController::class);
        Route::post('marketing', [MarketingController::class, 'store']);
    });
});