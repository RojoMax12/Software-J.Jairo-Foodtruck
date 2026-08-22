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
            $table->id('id_caja');
            $table->unsignedBigInteger('id_usuario');
            $table->dateTime('fecha_apertura');
            $table->decimal('monto_inicial', 8, 2);
            $table->integer('total_ventas');
            $table->integer('total_recaudado');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};
