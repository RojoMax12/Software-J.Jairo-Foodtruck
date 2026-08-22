<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_atencion', function (Blueprint $table) {
            $table->id('id_horario_atencion');
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->integer('minuto_colchon');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_atencion');
    }
};
