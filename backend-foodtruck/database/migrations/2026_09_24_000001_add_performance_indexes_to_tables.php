<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexesToTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper seguro para crear índices en PostgreSQL si no existen
        $createIndexIfNotExist = function (string $table, string $indexName, array $columns) {
            try {
                if (Schema::hasTable($table)) {
                    $cols = implode(', ', array_map(fn($col) => "\"$col\"", $columns));
                    DB::statement("CREATE INDEX IF NOT EXISTS \"{$indexName}\" ON \"{$table}\" ({$cols})");
                }
            } catch (\Throwable $e) {
                // Si la tabla no existe o el índice ya existe, continuar
            }
        };

        // 1. Productos
        $createIndexIfNotExist('productos', 'idx_productos_id_categoria', ['id_categoria']);
        $createIndexIfNotExist('productos', 'idx_productos_activo_disponible', ['activo', 'disponible']);

        // 2. Pedidos
        $createIndexIfNotExist('pedidos', 'idx_pedidos_id_estado_pedido', ['id_estado_pedido']);
        $createIndexIfNotExist('pedidos', 'idx_pedidos_id_estado_pago', ['id_estado_pago']);
        $createIndexIfNotExist('pedidos', 'idx_pedidos_fecha', ['fecha']);
        $createIndexIfNotExist('pedidos', 'idx_pedidos_id_usuario', ['id_usuario']);
        $createIndexIfNotExist('pedidos', 'idx_pedidos_numero_dia', ['numero_dia']);

        // 3. Detalle Pedido
        $createIndexIfNotExist('detalle_pedido', 'idx_detalle_pedido_id_pedido', ['id_pedido']);
        $createIndexIfNotExist('detalle_pedido', 'idx_detalle_pedido_id_producto', ['id_producto']);
        $createIndexIfNotExist('detalle_pedido', 'idx_detalle_pedido_id_tamano', ['id_tamaño']);

        // 4. Detalle Pedido Ingrediente
        $createIndexIfNotExist('detalle_pedido_ingrediente', 'idx_dpi_id_detalle_pedido', ['id_detalle_pedido']);
        $createIndexIfNotExist('detalle_pedido_ingrediente', 'idx_dpi_id_ingrediente', ['id_ingrediente']);

        // 5. Producto Tamaño
        $createIndexIfNotExist('producto_tamaño', 'idx_pt_prod_tamano', ['id_producto', 'id_tamaño']);

        // 6. Producto Ingrediente
        $createIndexIfNotExist('producto_ingrediente', 'idx_pi_prod_ingrediente', ['id_producto', 'id_ingrediente']);

        // 7. Movimientos e Inventario
        $createIndexIfNotExist('movimientos', 'idx_movimientos_id_ingrediente', ['id_ingrediente']);
        $createIndexIfNotExist('movimientos', 'idx_movimientos_fecha_mov', ['fecha_movimiento']);
        $createIndexIfNotExist('historial_movimientos', 'idx_historial_tipo', ['tipo']);
        $createIndexIfNotExist('historial_movimientos', 'idx_historial_created_at', ['created_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIndexIfExists = function (string $indexName) {
            try {
                DB::statement("DROP INDEX IF EXISTS \"{$indexName}\"");
            } catch (\Throwable $e) {
            }
        };

        $dropIndexIfExists('idx_productos_id_categoria');
        $dropIndexIfExists('idx_productos_activo_disponible');
        $dropIndexIfExists('idx_pedidos_id_estado_pedido');
        $dropIndexIfExists('idx_pedidos_id_estado_pago');
        $dropIndexIfExists('idx_pedidos_fecha');
        $dropIndexIfExists('idx_pedidos_id_usuario');
        $dropIndexIfExists('idx_pedidos_numero_dia');
        $dropIndexIfExists('idx_detalle_pedido_id_pedido');
        $dropIndexIfExists('idx_detalle_pedido_id_producto');
        $dropIndexIfExists('idx_detalle_pedido_id_tamano');
        $dropIndexIfExists('idx_dpi_id_detalle_pedido');
        $dropIndexIfExists('idx_dpi_id_ingrediente');
        $dropIndexIfExists('idx_pt_prod_tamano');
        $dropIndexIfExists('idx_pi_prod_ingrediente');
        $dropIndexIfExists('idx_movimientos_id_ingrediente');
        $dropIndexIfExists('idx_movimientos_fecha_mov');
        $dropIndexIfExists('idx_historial_tipo');
        $dropIndexIfExists('idx_historial_created_at');
    }
}
