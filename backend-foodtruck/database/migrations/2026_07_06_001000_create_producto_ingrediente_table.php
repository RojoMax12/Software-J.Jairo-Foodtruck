<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_ingrediente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_ingrediente');
            $table->unsignedBigInteger('id_tamaño')->nullable();
            $table->integer('cantidad')->default(1);
            $table->boolean('incluido_por_defecto')->default(true);
            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->foreign('id_ingrediente')->references('id_ingrediente')->on('ingredientes')->onDelete('cascade');
            $table->foreign('id_tamaño')->references('id_tamaño')->on('tamaños')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_ingrediente');
    }
};
