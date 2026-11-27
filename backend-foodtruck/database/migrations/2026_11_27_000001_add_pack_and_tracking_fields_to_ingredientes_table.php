<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredientes', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredientes', 'tipo_inventario')) {
                $table->string('tipo_inventario', 30)->default('unidad');
            }
            if (!Schema::hasColumn('ingredientes', 'unidades_por_pack')) {
                $table->integer('unidades_por_pack')->default(1);
            }
            if (!Schema::hasColumn('ingredientes', 'es_cuantificable')) {
                $table->boolean('es_cuantificable')->default(true);
            }
        });

        // Insumos típicamente no cuantificables por unidad (aderezos, salsas, especias/hojuelas)
        $noCuantificables = [
            'Mayonesa', 'Ketchup', 'Mostaza', 'Mayonesa de ajo',
            'Salsa BBQ', 'Salsa Americana', 'Salsa de queso', 'Salsa de ají',
            'Salsa verde', 'Salsa de tomate', 'Salsa cheddar',
            'Oregano', 'Orégano', 'Ciboulette', 'Chucrut',
        ];

        DB::table('ingredientes')
            ->whereIn('nombre', $noCuantificables)
            ->update([
                'tipo_inventario' => 'no_cuantitativo',
                'unidades_por_pack' => 1,
                'es_cuantificable' => false,
            ]);
    }

    public function down(): void
    {
        Schema::table('ingredientes', function (Blueprint $table) {
            if (Schema::hasColumn('ingredientes', 'es_cuantificable')) {
                $table->dropColumn('es_cuantificable');
            }
            if (Schema::hasColumn('ingredientes', 'unidades_por_pack')) {
                $table->dropColumn('unidades_por_pack');
            }
            if (Schema::hasColumn('ingredientes', 'tipo_inventario')) {
                $table->dropColumn('tipo_inventario');
            }
        });
    }
};

