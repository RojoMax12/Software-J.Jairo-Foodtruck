<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_movimientos', function (Blueprint $table) {
            $table->id('id_historial');
            $table->string('tipo', 50)->default('producto'); // producto, categoria, tamaño, oferta, stock, precio, caja
            $table->string('accion', 50)->default('crear'); // crear, editar, eliminar, oferta, estado, egreso, ingreso
            $table->string('descripcion', 255);
            $table->string('entidad', 255);
            $table->text('detalle')->nullable();
            $table->string('usuario', 150)->nullable()->default('Administrador');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->decimal('monto', 12, 2)->nullable()->default(0);
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_movimientos');
    }
};
