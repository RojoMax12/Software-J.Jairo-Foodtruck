<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->unsignedBigInteger('id_estado_pedido');
            $table->unsignedBigInteger('id_estado_pago');
            $table->unsignedBigInteger('id_usuario');
            $table->integer('numero_pedido_dia');
            $table->string('nombre_persona');
            $table->string('numero_telefono');
            $table->string('metodo_pago');
            $table->dateTime('fecha_de_pago')->nullable();
            $table->dateTime('fecha');
            $table->integer('total');
            $table->string('notas')->nullable();
            $table->timestamps();

            $table->foreign('id_estado_pedido')->references('id_estado_pedido')->on('estado_pedido')->onDelete('restrict');
            $table->foreign('id_estado_pago')->references('id_estado_pago')->on('estado_pago')->onDelete('restrict');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
