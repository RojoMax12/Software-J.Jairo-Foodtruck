<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dateTime('fecha_cierre')->nullable()->after('fecha_apertura');
            $table->string('estado', 20)->default('abierta')->after('total_recaudado');
            $table->integer('efectivo_esperado')->default(0)->after('estado');
            $table->integer('diferencia')->default(0)->after('efectivo_esperado');
            $table->integer('ventas_efectivo')->default(0)->after('diferencia');
            $table->integer('ventas_debito')->default(0)->after('ventas_efectivo');
            $table->integer('ventas_transferencia')->default(0)->after('ventas_debito');
            $table->integer('gastos_efectivo')->default(0)->after('ventas_transferencia');
            $table->text('observaciones')->nullable()->after('gastos_efectivo');
        });
    }

    public function down(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_cierre',
                'estado',
                'efectivo_esperado',
                'diferencia',
                'ventas_efectivo',
                'ventas_debito',
                'ventas_transferencia',
                'gastos_efectivo',
                'observaciones',
            ]);
        });
    }
};

