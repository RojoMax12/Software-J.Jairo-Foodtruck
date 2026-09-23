<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'imagen_ajuste')) {
                $table->string('imagen_ajuste', 10)->default('cover')->after('imagen_zoom');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'imagen_ajuste')) {
                $table->dropColumn('imagen_ajuste');
            }
        });
    }
};