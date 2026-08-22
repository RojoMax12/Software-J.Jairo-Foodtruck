<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pedidos', 'inventario_descontado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->boolean('inventario_descontado')->default(false)->after('notas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'inventario_descontado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropColumn('inventario_descontado');
            });
        }
    }
};
