<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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

                $table->foreign('id_categoria')->references('id')->on('categorias')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
