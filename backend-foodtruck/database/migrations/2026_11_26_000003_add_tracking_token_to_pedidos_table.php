<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'tracking_token')) {
                $table->string('tracking_token', 64)->nullable()->unique()->after('numero_pedido_dia');
            }
        });

        // Poblar tokens existentes para pedidos históricos sin token
        DB::table('pedidos')
            ->whereNull('tracking_token')
            ->orderBy('id_pedido')
            ->select('id_pedido')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('pedidos')
                        ->where('id_pedido', $row->id_pedido)
                        ->update(['tracking_token' => Str::random(24)]);
                }
            }, 'id_pedido');
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'tracking_token')) {
                $table->dropUnique(['tracking_token']);
                $table->dropColumn('tracking_token');
            }
        });
    }
};

