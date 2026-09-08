<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->string('regalo_presentacion', 20)->nullable()->after('subtotal');
            $table->decimal('regalo_costo', 10, 2)->default(0)->after('regalo_presentacion');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropColumn(['regalo_presentacion', 'regalo_costo']);
        });
    }
};
