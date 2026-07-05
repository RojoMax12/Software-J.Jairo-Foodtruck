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
        if (! Schema::hasTable('estado_pedido')) {
            Schema::create('estado_pedido', function (Blueprint $table) {
                $table->id('id_estado_pedido');
                $table->string('nombre')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_rol');
                $table->string('nombre');
                $table->string('correo')->unique();
                $table->boolean('estado')->default(true);
                $table->string('contrasena');
                $table->timestamps();
            });
        }

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

                $table->foreign('id_estado_pedido')->references('id_estado_pedido')->on('estado_pedido')->onDelete('restrict');
                $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
