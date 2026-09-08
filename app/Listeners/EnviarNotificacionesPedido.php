<?php
namespace App\Listeners;

use App\Events\PedidoCreado;
use App\Models\User;
use App\Notifications\PedidoConfirmacion;
use App\Notifications\NuevoPedidoAdmin;
use App\Services\NotificacionSegura;

class EnviarNotificacionesPedido
{
    public function handle(PedidoCreado $event): void
    {

        NotificacionSegura::enviar($event->pedido->user, new PedidoConfirmacion($event->pedido));

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            NotificacionSegura::enviar($admin, new NuevoPedidoAdmin($event->pedido));
        }
    }
}
