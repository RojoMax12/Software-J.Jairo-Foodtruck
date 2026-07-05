<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->integer('cantidad_actual')->default(0);
            $table->integer('cantidad_minima')->default(0);
            $table->dateTime('fecha_de_ingreso');
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });
    }


    // Eliminar la tabla ingredientes
    public function down(): void
    {
        Schema::dropIfExists('ingredientes');
    }
};