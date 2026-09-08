<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        DB::table('fragancias')->orderBy('id')->chunkById(100, function ($fragancias) {
            foreach ($fragancias as $f) {
                $primerTamano = trim(explode(',', $f->tamano_ml ?: '50 ml')[0]);
                if ($primerTamano === '') {
                    $primerTamano = '50 ml';
                }

                DB::table('fragancia_tamanos')->insert([
                    'fragancia_id'     => $f->id,
                    'tamano'           => $primerTamano,
                    'precio'           => $f->precio,
                    'precio_especial'  => $f->precio_especial,
                    'stock'            => $f->stock,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('fragancia_tamanos')->truncate();
    }
};
