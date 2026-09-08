<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fragancia_tamanos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fragancia_id')->constrained('fragancias')->onDelete('cascade');
            $table->string('tamano', 20);
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_especial', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->unique(['fragancia_id', 'tamano']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fragancia_tamanos');
    }
};
