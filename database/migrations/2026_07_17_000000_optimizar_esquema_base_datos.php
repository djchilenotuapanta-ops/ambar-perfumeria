<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index(['estado', 'created_at'], 'pedidos_estado_created_at_index');
        });

        Schema::table('carrito', function (Blueprint $table) {
            $table->unique(['user_id', 'fragancia_tamano_id'], 'carrito_user_tamano_unique');
        });

        Schema::table('fragancias', function (Blueprint $table) {
            $table->dropColumn('tamano_ml');
        });

        Schema::table('carrito', function (Blueprint $table) {
            $table->dropColumn('tamano');
        });
    }

    public function down(): void
    {
        Schema::table('carrito', function (Blueprint $table) {
            $table->string('tamano', 20)->nullable();
        });

        Schema::table('fragancias', function (Blueprint $table) {
            $table->string('tamano_ml', 50)->nullable();
        });

        Schema::table('carrito', function (Blueprint $table) {
            $table->dropUnique('carrito_user_tamano_unique');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex('pedidos_estado_created_at_index');
        });
    }
};
