<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id('id_oferta');
            $table->unsignedBigInteger('id_productos');
            $table->string('descripcion');
            $table->integer('precio_oferta');
            $table->string('tipo');
            $table->dateTime('fecha');
            $table->string('dia_semana');
            $table->timestamps();

            $table->foreign('id_productos')->references('id_producto')->on('productos')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
