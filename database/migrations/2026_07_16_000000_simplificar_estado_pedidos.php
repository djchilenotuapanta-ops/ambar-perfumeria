<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        DB::table('pedidos')
            ->whereIn('estado', ['confirmado', 'preparando', 'enviado'])
            ->update(['estado' => 'pendiente']);

        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'entregado', 'cancelado'])
                  ->default('pendiente')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'confirmado', 'preparando', 'enviado', 'entregado', 'cancelado'])
                  ->default('pendiente')
                  ->change();
        });
    }
};
