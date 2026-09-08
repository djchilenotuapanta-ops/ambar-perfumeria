<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('user_id', 'pedidos_user_id_index');
        });

        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->index('pedido_id', 'pedido_detalles_pedido_id_index');
            $table->index('fragancia_id', 'pedido_detalles_fragancia_id_index');
            $table->index('fragancia_tamano_id', 'pedido_detalles_fragancia_tamano_id_index');
        });

        Schema::table('carrito', function (Blueprint $table) {
            $table->index('fragancia_id', 'carrito_fragancia_id_index');
        });

        Schema::table('fragancias', function (Blueprint $table) {
            $table->index('familia_id', 'fragancias_familia_id_index');
        });

        Schema::table('perfiles_cliente', function (Blueprint $table) {
            $table->unique('user_id', 'perfiles_cliente_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('perfiles_cliente', function (Blueprint $table) {
            $table->dropUnique('perfiles_cliente_user_id_unique');
        });

        Schema::table('fragancias', function (Blueprint $table) {
            $table->dropIndex('fragancias_familia_id_index');
        });

        Schema::table('carrito', function (Blueprint $table) {
            $table->dropIndex('carrito_fragancia_id_index');
        });

        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropIndex('pedido_detalles_pedido_id_index');
            $table->dropIndex('pedido_detalles_fragancia_id_index');
            $table->dropIndex('pedido_detalles_fragancia_tamano_id_index');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex('pedidos_user_id_index');
        });
    }
};
