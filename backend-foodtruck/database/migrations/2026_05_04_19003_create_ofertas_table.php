<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_productos')->constrained('productos')->onDelete('restrict');
            $table->string('descripcion');
            $table->decimal('precio_oferta', 10, 2);
            $table->string('tipo');
            $table->dateTime('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};