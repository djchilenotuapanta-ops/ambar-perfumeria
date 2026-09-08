<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('carrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fragancia_id')->constrained('fragancias')->onDelete('cascade');
            $table->string('tamano', 20)->nullable();
            $table->integer('cantidad')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('carrito'); }
};
