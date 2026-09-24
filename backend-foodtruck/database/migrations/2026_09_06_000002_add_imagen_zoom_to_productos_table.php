<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'imagen_zoom')) {
                $table->decimal('imagen_zoom', 3, 2)->default(1.00)->after('imagen_posicion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'imagen_zoom')) {
                $table->dropColumn('imagen_zoom');
            }
        });
    }
};