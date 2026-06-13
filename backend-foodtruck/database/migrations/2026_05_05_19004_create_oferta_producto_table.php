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
            $table->foreignId('id_productos')->constrained('productos')->onDelete('cascade');
            $table->string('descripcion');
            $table->integer('precio_oferta');
            $table->string('tipo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_producto');
    }
};