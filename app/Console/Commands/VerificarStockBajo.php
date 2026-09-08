<?php
namespace App\Console\Commands;

use App\Events\StockBajoDetectado;
use App\Models\FraganciaTamano;
use Illuminate\Console\Command;

class VerificarStockBajo extends Command
{
    protected $signature = 'inventario:verificar-stock-bajo {--umbral=}';
    protected $description = 'Verifica fragancias con stock bajo y envía notificaciones a los admins';

    public function handle(): int
    {
        $umbral = $this->option('umbral') !== null
            ? (int) $this->option('umbral')
            : (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));

        $tamanosBajos = FraganciaTamano::with('fragancia')
            ->where('stock', '<=', $umbral)
            ->whereHas('fragancia', fn ($q) => $q->where('activo', true))
            ->get();

        if ($tamanosBajos->isEmpty()) {
            $this->info('✓ No hay fragancias con stock bajo ni agotadas');
            return self::SUCCESS;
        }

        // Solo se avisa de los tamaños que todavía no fueron notificados a
        // este nivel de stock; evita reenviar el mismo aviso cada día
        // mientras nadie reponga el producto (ver migración
        // add_alerta_stock_enviada_a_fragancia_tamanos).
        $porNotificar = $tamanosBajos->whereNull('alerta_stock_enviada_at');

        $this->info("Se encontraron {$tamanosBajos->count()} tamaño(s) con stock bajo o agotado ({$porNotificar->count()} sin notificar aún):");

        foreach ($tamanosBajos as $tamano) {
            $this->line("  • {$tamano->fragancia->nombre} ({$tamano->tamano}): {$tamano->stock} unidades");
        }

        foreach ($porNotificar as $tamano) {
            StockBajoDetectado::dispatch($tamano->fragancia, $tamano->stock, $umbral);
            $tamano->alerta_stock_enviada_at = now();
            $tamano->saveQuietly();
        }

        $this->info("\n✓ Notificaciones enviadas a los administradores.");
        return self::SUCCESS;
    }
}
