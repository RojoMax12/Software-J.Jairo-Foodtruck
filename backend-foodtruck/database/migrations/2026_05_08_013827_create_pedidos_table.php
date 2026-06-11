<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion');
            $table->unsignedBigInteger('id_estado_pedido');
            $table->unsignedBigInteger('id_usuario_dicreme')->nullable();
            $table->unsignedBigInteger('id_usuario_distribuidor');
            $table->date('fecha_creacion');
            $table->time('hora_creacion');
            $table->integer('monto_estimado');
            $table->integer('monto_final');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
