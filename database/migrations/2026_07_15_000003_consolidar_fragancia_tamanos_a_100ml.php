<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        $porFragancia = DB::table('fragancia_tamanos')
            ->orderBy('id')
            ->get()
            ->groupBy('fragancia_id');

        foreach ($porFragancia as $fraganciaId => $tamanos) {
            $conservar = $tamanos->firstWhere('tamano', '100 ml') ?? $tamanos->first();

            $stockTotal = (int) $tamanos->sum('stock');
            $precioMinimo = (float) $tamanos->min('precio');

            $precioEspecial = $conservar->precio_especial;

            DB::table('fragancia_tamanos')->where('id', $conservar->id)->update([
                'tamano'          => '100 ml',
                'precio'          => $precioMinimo,
                'precio_especial' => $precioEspecial,
                'stock'           => $stockTotal,
                'updated_at'      => now(),
            ]);

            $idsAEliminar = $tamanos->pluck('id')->reject(fn ($id) => $id === $conservar->id)->all();
            if (!empty($idsAEliminar)) {
                DB::table('carrito')->whereIn('fragancia_tamano_id', $idsAEliminar)->delete();
                DB::table('pedido_detalles')->whereIn('fragancia_tamano_id', $idsAEliminar)->update(['fragancia_tamano_id' => null]);
                DB::table('fragancia_tamanos')->whereIn('id', $idsAEliminar)->delete();
            }
        }
    }

    public function down(): void
    {

    }
};
