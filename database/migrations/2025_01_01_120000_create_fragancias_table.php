<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fragancias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('slug', 180)->unique();
            $table->text('descripcion');
            $table->string('casa_perfumista', 100);
            $table->enum('genero', ['mujer','hombre','unisex'])->default('unisex');
            $table->string('notas_salida')->nullable();
            $table->string('notas_corazon')->nullable();
            $table->string('notas_fondo')->nullable();
            $table->string('tamano_ml', 50)->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_especial', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('imagen_principal')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('destacado')->default(false);
            $table->boolean('nuevo')->default(false);
            $table->foreignId('familia_id')->constrained('familias')->onDelete('restrict');
            $table->timestamps();

            $table->index(['activo', 'genero']);
            $table->index(['activo', 'destacado']);
            $table->index(['activo', 'nuevo']);
        });
    }
    public function down(): void { Schema::dropIfExists('fragancias'); }
};
