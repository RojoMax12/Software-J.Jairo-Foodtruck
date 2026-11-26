<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite asociar una promoción a un tamaño específico (o null para aplicar a todos).
     */
    public function up(): void
    {
        Schema::table('promociones', function (Blueprint $table) {
            if (!Schema::hasColumn('promociones', 'id_tamaño')) {
                $table->unsignedBigInteger('id_tamaño')->nullable()->after('id_producto');
                $table->foreign('id_tamaño')->references('id_tamaño')->on('tamaños')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('promociones', function (Blueprint $table) {
            if (Schema::hasColumn('promociones', 'id_tamaño')) {
                $table->dropForeign(['id_tamaño']);
                $table->dropColumn('id_tamaño');
            }
        });
    }
};

