<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_ingrediente', function (Blueprint $table) {
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
            $table->foreignId('id_ingrediente')->constrained('ingredientes')->onDelete('cascade');
            $table->primary(['id_producto', 'id_ingrediente']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_ingrediente');
    }
};