<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solo admin accede a caja.
     */
    public function up(): void
    {
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->constrained('ventas')->onDelete('restrict');
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('restrict');
            $table->dateTime('fecha_apertura');
            $table->integer('total_ventas');
            $table->integer('total_recaudado');
            $table->timestamps();
        });
    }

    // Eliminar la tabla caja
    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};