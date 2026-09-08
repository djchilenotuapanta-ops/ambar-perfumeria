<?php

namespace App\Events;

use App\Models\Fragancia;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockBajoDetectado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Fragancia $fragancia,
        public int $stockActual,
        public int $umbral = 10,
    ) {
    }
}
