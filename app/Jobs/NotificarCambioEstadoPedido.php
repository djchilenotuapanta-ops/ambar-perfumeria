<?php

namespace App\Jobs;

use App\Models\Pedido;
use App\Models\User;
use App\Notifications\PedidoCanceladoAdmin;
use App\Notifications\PedidoEstadoCambiado;
use App\Services\NotificacionSegura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NotificarCambioEstadoPedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Pedido $pedido,
        public string $estadoAnterior,
        public string $estadoNuevo,
    ) {}

    public function handle(): void
    {
        // Notificar al cliente del cambio de estado
        NotificacionSegura::enviar(
            $this->pedido->user,
            new PedidoEstadoCambiado($this->pedido, $this->estadoAnterior, $this->estadoNuevo)
        );

        // Si se canceló, restaurar stock y notificar admins
        if ($this->estadoNuevo === 'cancelado') {
            $this->restaurarStock();
            $this->notificarAdmins(new PedidoCanceladoAdmin($this->pedido));
        }
    }

    private function restaurarStock(): void
    {
        if ($this->pedido->stock_restaurado) {
            return;
        }

        DB::transaction(function () {
            $detalles = $this->pedido->detalles()->lockForUpdate()->get();

            foreach ($detalles as $detalle) {
                if (!$detalle->fragancia_tamano_id) {
                    continue;
                }

                $tamano = $detalle->tamano()->lockForUpdate()->first();
                if (!$tamano) {
                    continue;
                }

                $tamano->stock += $detalle->cantidad;
                $tamano->save();
            }

            DB::table('pedidos')->where('id', $this->pedido->id)->update(['stock_restaurado' => true]);
        });
    }

    private function notificarAdmins($notificacion): void
    {
        foreach (User::where('role', 'admin')->get() as $admin) {
            NotificacionSegura::enviar($admin, $notificacion);
        }
    }
}
