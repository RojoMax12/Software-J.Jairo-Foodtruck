<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_categoria');
                $table->string('tipo_armado');
                $table->integer('cantidad_incluida');
                $table->decimal('precio_ingrediente_extra', 8, 2);
                $table->string('nombre');
                $table->string('descripcion');
                $table->timestamps();
            });
        }

        Schema::create('oferta_producto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_productos');
            $table->string('descripcion');
            $table->integer('precio_oferta');
            $table->string('tipo');
            $table->timestamps();

            $table->foreign('id_productos')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_producto');
    }
};