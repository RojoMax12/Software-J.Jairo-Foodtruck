<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->unsignedBigInteger('id_ingrediente');
            $table->decimal('cantidad', 8, 2);
            $table->string('tipo_movimiento');
            $table->date('fecha_movimiento');
            $table->timestamps();

            $table->foreign('id_ingrediente')->references('id_ingrediente')->on('ingredientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
