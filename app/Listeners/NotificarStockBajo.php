<?php

namespace App\Listeners;

use App\Events\StockBajoDetectado;
use App\Models\User;
use App\Notifications\StockBajo;
use App\Services\NotificacionSegura;

class NotificarStockBajo
{
    public function handle(StockBajoDetectado $event): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            NotificacionSegura::enviar($admin, new StockBajo($event->fragancia, $event->stockActual, $event->umbral));
        }
    }
}
