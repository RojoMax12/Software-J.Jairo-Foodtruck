<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_tamaño', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_tamaño');
            $table->decimal('precio', 8, 2);
            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->foreign('id_tamaño')->references('id_tamaño')->on('tamaños')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_tamaño');
    }
};
