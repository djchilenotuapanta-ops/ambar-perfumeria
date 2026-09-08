<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fragancia_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fragancia_id')->constrained('fragancias')->onDelete('cascade');
            $table->string('ruta_imagen');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('fragancia_imagenes'); }
};
