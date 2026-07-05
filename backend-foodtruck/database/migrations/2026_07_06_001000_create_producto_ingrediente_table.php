<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_ingrediente', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_ingrediente');
            $table->boolean('incluido_por_defecto')->default(true);
            $table->timestamps();

            $table->primary(['id_producto', 'id_ingrediente']);
            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->foreign('id_ingrediente')->references('id_ingrediente')->on('ingredientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_ingrediente');
    }
};
