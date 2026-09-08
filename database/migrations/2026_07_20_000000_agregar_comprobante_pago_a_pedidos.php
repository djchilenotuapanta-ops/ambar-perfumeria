<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pedidos', function (Blueprint $table) {

            $table->string('comprobante_pago')->nullable()->after('pago_metodo');
            $table->timestamp('comprobante_subido_at')->nullable()->after('comprobante_pago');
        });
    }
    public function down(): void {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['comprobante_pago', 'comprobante_subido_at']);
        });
    }
};
