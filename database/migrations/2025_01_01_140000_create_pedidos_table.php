<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido', 20)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente','confirmado','preparando','enviado','entregado','cancelado'])
                  ->default('pendiente');
            $table->enum('pago_estado', ['pendiente','pagado','reembolsado'])->default('pendiente');
            $table->string('pago_metodo', 50)->nullable();
            $table->string('direccion_envio')->nullable();
            $table->string('ciudad_envio', 100)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pedidos'); }
};
