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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_estado_pedido')->constrained('estado_pedido', 'id_pedido')->onDelete('restrict');
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('restrict');
            $table->string('nombre_persona');
            $table->string('numero_telefono');
            $table->string('metodo_pago');
            //Estado del pago, pagado o no pagado - true: pagado, false: no pagado
            $table->boolean('estado_pago');
            $table->dateTime('fecha');
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
