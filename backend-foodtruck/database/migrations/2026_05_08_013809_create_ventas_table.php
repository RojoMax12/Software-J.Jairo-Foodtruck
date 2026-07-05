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
        if (! Schema::hasTable('pedidos')) {
            Schema::create('pedidos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_estado_pedido');
                $table->unsignedBigInteger('id_usuario');
                $table->string('nombre_persona');
                $table->string('numero_telefono');
                $table->string('metodo_pago');
                $table->boolean('estado_pago');
                $table->dateTime('fecha');
                $table->integer('total');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pedido');
                $table->timestamps();

                $table->foreign('id_pedido')->references('id')->on('pedidos')->onDelete('restrict');
            });
        }
    } // Aquí cierra correctamente el método up()

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('pedidos'); // Buena práctica: eliminar también pedidos si se revierte
    }
};