<?php

namespace App\Jobs;

use App\Models\Pedido;
use App\Models\User;
use App\Notifications\PagoConfirmadoAdmin;
use App\Notifications\PagoConfirmadoCliente;
use App\Services\NotificacionSegura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificarPagoConfirmado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function handle(): void
    {
        // Notificar al cliente
        NotificacionSegura::enviar($this->pedido->user, new PagoConfirmadoCliente($this->pedido));

        // Notificar a todos los admins
        foreach (User::where('role', 'admin')->get() as $admin) {
            NotificacionSegura::enviar($admin, new PagoConfirmadoAdmin($this->pedido));
        }
    }
}
