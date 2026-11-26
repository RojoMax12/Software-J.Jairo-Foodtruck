<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite definir un precio extra específico por ingrediente agregado (ej. Carne +$1.500, Tocino +$1.000).
     * Si es null, se usa precio_ingrediente_extra del producto.
     */
    public function up(): void
    {
        Schema::table('producto_ingrediente', function (Blueprint $table) {
            if (!Schema::hasColumn('producto_ingrediente', 'precio_extra')) {
                $table->unsignedInteger('precio_extra')->nullable()->after('incluido_por_defecto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('producto_ingrediente', function (Blueprint $table) {
            if (Schema::hasColumn('producto_ingrediente', 'precio_extra')) {
                $table->dropColumn('precio_extra');
            }
        });
    }
};

