<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FormatoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\Pedido_productoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Usuario_dicremeController;
use App\Http\Controllers\Usuario_distribuidoresController;
use App\Http\Controllers\DespachoController;
use App\Http\Controllers\Estado_pedidoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\Cotizacion_productoController;
use App\Http\Controllers\Estado_cotizacionController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. AUTENTICACIÓN (LOGIN)
// ==========================================
Route::prefix('auth')->middleware('throttle:auth_limits')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ==========================================
// 2. RUTAS PÚBLICAS O DE SÓLO REGISTRO (Sin Token)
// ==========================================
Route::middleware('throttle:api_escritura')->group(function () {
    // Permitimos que cualquiera se cree una cuenta de distribuidor sin estar logueado aún
    Route::post('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'store']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('throttle:api_lectura')->group(function() {

	Route::get('/productos', [ProductoController::class, 'index']);
	Route::get('/productos/{id}', [ProductoController::class, 'show']);

	Route::get('/categorias', [CategoriaController::class, 'index']);
	Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
	Route::get('/formatos', [FormatoController::class, 'index']);
	Route::get('/formatos/{id}', [FormatoController::class, 'show']);
});


// ==========================================
// 3. ENTORNO PROTEGIDO - REQUIERE INICIO DE SESIÓN JWT
// ==========================================
Route::middleware('jwt.auth')->group(function () {

    // --- Sub-Grupo de Escritura (POST, PUT, DELETE) ---
    Route::middleware('throttle:api_escritura')->group(function () {
        
        // Mover aquí las escrituras de Cotizaciones para que tengan JWT previo
        Route::post('/cotizaciones', [CotizacionController::class, 'store'])->middleware('role:1,2,3');
        Route::put('/cotizaciones/{id_cotizacion}/tomarcotizacion/{id_admin}', [CotizacionController::class, 'tomarCotizacion'])->middleware('role:1');
       
        Route::post('/cotizaciones/{id_cotizacion}/agregarproductos', [CotizacionController::class, 'addproductocotizacion'])->middleware('role:1,2,3');
        Route::post('/cotizaciones/{id_cotizacion}/eliminarproductos', [CotizacionController::class, 'removeproductocotizacion'])->middleware('role:1,2,3');
        Route::post('/cotizaciones/{id_cotizacion}/eliminarproducto', [CotizacionController::class, 'destroyProductoCotizacion'])->middleware('role:1,3');
        
        Route::put('/cotizaciones/{id_cotizacion}/dejarcotizacion/{id_admin}', [CotizacionController::class, 'DejarCotizacion'])->middleware('role:1');
        Route::put('/cotizaciones/{id_cotizacion}/cancelarcotizacion/{id_usuario}', [CotizacionController::class, 'cancelarCotizacion'])->middleware('role:1,3');
        Route::put('/cotizaciones/{id_cotizacion}/validarcotizacion/{id_admin}', [CotizacionController::class, 'validarCotizacion'])->middleware('role:1');
        Route::put('/cotizaciones/{id}', [CotizacionController::class, 'update'])->middleware('role:1,2,3');
        Route::put('/cotizaciones/{id}/total', [CotizacionController::class, 'updateTotal'])->middleware('role:1,2,3');
        Route::delete('/cotizaciones/{id}', [CotizacionController::class, 'destroy'])->middleware('role:1');
        Route::delete('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'destroy'])->middleware('role:1');
        Route::post('/usuarios_dicreme', [Usuario_dicremeController::class, 'store'])->middleware('role:1');

        // Mantenemos el resto de tus rutas de escritura protegidas
        Route::post('/categorias', [CategoriaController::class, 'store'])->middleware('role:1');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->middleware('role:1');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->middleware('role:1');
        Route::post('/formatos', [FormatoController::class, 'store'])->middleware('role:1');
        Route::put('/formatos/{id}', [FormatoController::class, 'update'])->middleware('role:1');
        Route::delete('/formatos/{id}', [FormatoController::class, 'destroy'])->middleware('role:1');
        Route::post('/productos', [ProductoController::class, 'store'])->middleware('role:1');
        Route::put('/productos/{id}', [ProductoController::class, 'update'])->middleware('role:1');
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->middleware('role:1');
        Route::post('/roles', [RolController::class, 'store'])->middleware('role:1');
        Route::put('/roles/{id}', [RolController::class, 'update'])->middleware('role:1');
        Route::delete('/roles/{id}', [RolController::class, 'destroy'])->middleware('role:1');
        Route::put('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'update'])->middleware('role:1');
        Route::delete('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'destroy'])->middleware('role:1');
        Route::put('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'update'])->middleware('role:1');
        Route::delete('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'destroy'])->middleware('role:1');
        
        Route::post('/pedidos', [PedidoController::class, 'store']);
        Route::put('/pedidos/{id_pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])->middleware('role:1,2');
        Route::put('/pedidos/{id}', [PedidoController::class, 'update'])->middleware('role:1,2');
        Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy'])->middleware('role:1');
        Route::post('/estado_pedido', [Estado_pedidoController::class, 'store'])->middleware('role:1');
        Route::put('/estado_pedido/{id}', [Estado_pedidoController::class, 'update'])->middleware('role:1');
        Route::delete('/estado_pedido/{id}', [Estado_pedidoController::class, 'destroy'])->middleware('role:1');
        Route::post('/despachos', [DespachoController::class, 'store']);
        Route::put('/despachos/{id}', [DespachoController::class, 'update'])->middleware('role:1');
        Route::delete('/despachos/{id}', [DespachoController::class, 'destroy'])->middleware('role:1');
        Route::post('/bodegas', [BodegaController::class, 'store'])->middleware('role:1');
        Route::put('/bodegas/{id}', [BodegaController::class, 'update'])->middleware('role:1');
        Route::delete('/bodegas/{id}', [BodegaController::class, 'destroy'])->middleware('role:1');
        Route::post('/lotes', [LoteController::class, 'store']);
        Route::put('/lotes/{id}', [LoteController::class, 'update'])->middleware('role:1');
        Route::delete('/lotes/{id}', [LoteController::class, 'destroy'])->middleware('role:1');
        Route::post('/pedido_producto', [Pedido_productoController::class, 'store']);
        Route::put('/pedido_producto/{id}', [Pedido_productoController::class, 'update'])->middleware('role:1');
        Route::delete('/pedido_producto/{id}', [Pedido_productoController::class, 'destroy'])->middleware('role:1');
        Route::post('/stocks', [StockController::class, 'store']);
        Route::put('/stocks/{id}', [StockController::class, 'update'])->middleware('role:1');
        Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->middleware('role:1');
        Route::post('/ventas', [VentaController::class, 'store']);
        Route::put('/ventas/{id}', [VentaController::class, 'update'])->middleware('role:1');
        Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->middleware('role:1');
        Route::post('/cotizacion/{id}/transformar', [CotizacionController::class, 'transformarCotizacionEnPedido'])->middleware('role:1');
        Route::get('/cotizaciones/{id}/usuario_dicreme',[CotizacionController::class, 'getallCotizacionesByUsuariodicreme'])->middleware('role:1');
        Route::get('/pedidos/{id}/usuario_dicreme', [PedidoController::class, 'getallPedidosByUsuariodicreme'])->middleware('role:1');
        Route::post('/estado_cotizacion', [Estado_cotizacionController::class, 'store'])->middleware('role:1');
        Route::put('/estado_cotizacion/{id}', [Estado_cotizacionController::class, 'update'])->middleware('role:1');
        Route::delete('/estado_cotizacion/{id}', [Estado_cotizacionController::class, 'destroy'])->middleware('role:1');
    });

    // --- Sub-Grupo de Lectura (GET) ---
    Route::middleware('throttle:api_lectura')->group(function () {
        
        // Mover aquí las lecturas de Cotizaciones para que tengan JWT previo
        Route::get('/cotizaciones', [CotizacionController::class, 'index'])->middleware('role:1,2,3');
        Route::get('/cotizaciones/{id}/details', [CotizacionController::class, 'getdetailcotizacion'])->middleware('role:1,2,3');
        Route::get('/cotizaciones/{id}', [CotizacionController::class, 'show'])->middleware('role:1,2,3');
        Route::get('/cotizaciones/{id}/usuario_distribuidor', [CotizacionController::class, 'getallCotizacionesByUsuariodistribuidor'])->middleware('role:1,3');
        Route::get('/cotizacion_producto', [Cotizacion_productoController::class, 'index']);
        Route::get('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'show']);
        Route::get('/cotizacion_producto/cotizacion/{idCotizacion}', [Cotizacion_productoController::class, 'getByCotizacionId']);
        Route::get('/cotizacion_producto/producto/{idProducto}', [Cotizacion_productoController::class, 'getByProductoId']);

        // Mantenemos tus lecturas maestras
        
        Route::get('/estado_cotizacion', [Estado_cotizacionController::class, 'index'])->middleware('role:1');
        Route::get('/estado_cotizacion/{id}', [Estado_cotizacionController::class, 'show'])->middleware('role:1');
        Route::get('/roles', [RolController::class, 'index']);           
        Route::get('/roles/{id}', [RolController::class, 'show']);      
        Route::get('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'index']);      
        Route::get('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'show']);             
        Route::get('/usuarios_dicreme', [Usuario_dicremeController::class, 'index']);          
        Route::get('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'show']);        
        Route::get('/pedidos', [PedidoController::class, 'index']);
        Route::get('/pedidos/{id}/details', [PedidoController::class, 'getdetailpedido'])->middleware('role:1,2,3');
        Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
        Route::get('/pedidos/{id}/usuario_distribuidor',[PedidoController::class, 'getallPedidosByUsuariodistribuidor'])->middleware('role:1,3');
        Route::get('/estado_pedido', [Estado_pedidoController::class, 'index']);
        Route::get('/estado_pedido/{id}', [Estado_pedidoController::class, 'show']);
        Route::get('/despachos', [DespachoController::class, 'index']);
        Route::get('/despachos/{id}/pedidos', [DespachoController::class, 'getdespachobyidpedido']);
        Route::get('/despachos/{id}', [DespachoController::class, 'show']);
        Route::get('/bodegas', [BodegaController::class, 'index']);
        Route::get('/bodegas/{id}', [BodegaController::class, 'show']);
        Route::get('/lotes', [LoteController::class, 'index']);
        Route::get('/lotes/{id}', [LoteController::class, 'show']);
        Route::get('/pedido_producto', [Pedido_productoController::class, 'index']);
        Route::get('/pedido_producto/{id}', [Pedido_productoController::class, 'show']);
        Route::get('/stocks', [StockController::class, 'index']);
        Route::get('/stocks/{id}', [StockController::class, 'show']);
        Route::get('/ventas', [VentaController::class, 'index']);
        Route::get('/ventas/{id}', [VentaController::class, 'show']);
    });
    
});