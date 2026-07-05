<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedido_ingrediente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_detalle_pedido');
            $table->unsignedBigInteger('id_ingrediente');
            $table->string('tipo_modificacion');
            $table->decimal('precio_aplicado', 8, 2);
            $table->timestamps();

            $table->foreign('id_detalle_pedido')->references('id_detalle_pedido')->on('detalle_pedido')->onDelete('cascade');
            $table->foreign('id_ingrediente')->references('id_ingrediente')->on('ingredientes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_ingrediente');
    }
};
