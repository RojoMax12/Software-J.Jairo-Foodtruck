<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'imagen_posicion')) {
                $table->string('imagen_posicion', 30)->default('50% 50%')->after('imagen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'imagen_posicion')) {
                $table->dropColumn('imagen_posicion');
            }
        });
    }
};