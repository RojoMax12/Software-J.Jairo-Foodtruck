<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'activo')) {
                $table->boolean('activo')->default(true)->after('descripcion');
            }
            if (!Schema::hasColumn('productos', 'disponible')) {
                $table->boolean('disponible')->default(true)->after('activo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'activo')) {
                $table->dropColumn('activo');
            }
            if (Schema::hasColumn('productos', 'disponible')) {
                $table->dropColumn('disponible');
            }
        });
    }
};
