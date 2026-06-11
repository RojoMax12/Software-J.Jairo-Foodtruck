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
        Schema::create('usuarios_distribuidores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rol');
            $table->string('rut_empresa')->unique();
            $table->string('nombre_empresa');
            $table->string('contrasena');
            $table->string('direccion');
            $table->string('telefono');
            $table->string('correo_electronico')->unique();
            $table->string('comuna');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_distribuidores');
    }
};
