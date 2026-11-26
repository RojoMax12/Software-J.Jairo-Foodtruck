<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las ofertas deben poder definirse por producto Y por tamaño (reunión 11/10).
     * id_tamaño nulo = la oferta aplica a todos los tamaños del producto.
     */
    public function up(): void
    {
        Schema::table('oferta_producto', function (Blueprint $table) {
            if (!Schema::hasColumn('oferta_producto', 'id_tamaño')) {
                $table->unsignedBigInteger('id_tamaño')->nullable()->after('id_productos');
                $table->foreign('id_tamaño')->references('id_tamaño')->on('tamaños')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('oferta_producto', function (Blueprint $table) {
            if (Schema::hasColumn('oferta_producto', 'id_tamaño')) {
                $table->dropForeign(['id_tamaño']);
                $table->dropColumn('id_tamaño');
            }
        });
    }
};
