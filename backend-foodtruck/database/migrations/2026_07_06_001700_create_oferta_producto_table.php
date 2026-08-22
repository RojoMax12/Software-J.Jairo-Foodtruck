<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oferta_producto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_productos');
            $table->string('descripcion');
            $table->integer('precio_oferta');
            $table->string('tipo');
            $table->timestamps();

            $table->foreign('id_productos')->references('id_producto')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_producto');
    }
};
