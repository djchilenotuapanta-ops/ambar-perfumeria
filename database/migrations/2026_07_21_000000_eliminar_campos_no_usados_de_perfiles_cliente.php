<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('perfiles_cliente', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'whatsapp',
                'ciudad',
                'departamento',
                'total_pedidos',
                'total_gastado',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('perfiles_cliente', function (Blueprint $table) {
            $table->string('telefono', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->integer('total_pedidos')->default(0);
            $table->decimal('total_gastado', 12, 2)->default(0);
        });
    }
};
