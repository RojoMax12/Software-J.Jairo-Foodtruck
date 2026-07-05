<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SebastianBergmann\Type\TrueType;

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

        if (! Schema::hasTable('ingredientes')) {
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

        Schema::create('producto_ingrediente', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_ingrediente');
            $table->boolean('incluido_por_defecto')->default(true);
            $table->primary(['id_producto', 'id_ingrediente']);
            $table->timestamps();

            $table->foreign('id_producto')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('id_ingrediente')->references('id')->on('ingredientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_ingrediente');
    }
};