<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solo admin accede a caja.
     */
    public function up(): void
    {
        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_rol');
                $table->string('nombre');
                $table->string('correo')->unique();
                $table->boolean('estado')->default(true);
                $table->string('contrasena');
                $table->timestamps();
            });
        }

        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->dateTime('fecha_apertura');
            $table->decimal('monto_inicial', 8, 2);
            $table->integer('total_ventas');
            $table->integer('total_recaudado');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('restrict');
        });
    }

    // Eliminar la tabla caja
    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};