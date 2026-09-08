<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carrito', function (Blueprint $table) {
            $table->foreignId('fragancia_tamano_id')->nullable()->after('fragancia_id')
                  ->constrained('fragancia_tamanos')->onDelete('cascade');
        });

        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->foreignId('fragancia_tamano_id')->nullable()->after('fragancia_id')
                  ->constrained('fragancia_tamanos')->onDelete('set null');
        });

        DB::table('carrito')->truncate();
    }

    public function down(): void
    {
        Schema::table('carrito', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fragancia_tamano_id');
        });

        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fragancia_tamano_id');
        });
    }
};
