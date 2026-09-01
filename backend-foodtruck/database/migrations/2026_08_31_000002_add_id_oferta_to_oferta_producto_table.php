<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oferta_producto', function (Blueprint $table) {
            if (!Schema::hasColumn('oferta_producto', 'id_oferta')) {
                $table->unsignedBigInteger('id_oferta')->nullable()->after('id');
                $table->foreign('id_oferta')->references('id_oferta')->on('ofertas')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('oferta_producto', function (Blueprint $table) {
            if (Schema::hasColumn('oferta_producto', 'id_oferta')) {
                $table->dropForeign(['id_oferta']);
                $table->dropColumn('id_oferta');
            }
        });
    }
};

