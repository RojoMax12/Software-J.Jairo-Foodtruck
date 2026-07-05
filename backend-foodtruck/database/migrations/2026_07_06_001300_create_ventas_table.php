<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_caja');
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->onDelete('restrict');
            $table->foreign('id_caja')->references('id_caja')->on('caja')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
