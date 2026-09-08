<?php
namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PagoConfirmadoAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Pedido $pedido) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urlPedido = route('admin.pedidos.show', $this->pedido->id);

        return (new MailMessage)
            ->subject("💰 Venta confirmada — Pedido #{$this->pedido->numero_pedido}")
            ->greeting("Pago confirmado")
            ->line("Se confirmó el pago del pedido #{$this->pedido->numero_pedido}.")
            ->line("Cliente: {$this->pedido->user->name} ({$this->pedido->user->email})")
            ->line("Total: \${$this->pedido->total}")
            ->action('Ver Pedido', $urlPedido)
            ->line("Ambar - Sistema de Gestión");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'titulo'  => 'Venta confirmada',
            'mensaje' => "Pago confirmado del pedido #{$this->pedido->numero_pedido} — {$this->pedido->user->name} — \${$this->pedido->total}.",
            'icono'   => 'fas fa-circle-dollar-to-slot',
            'color'   => 'success',
            'url'     => route('admin.pedidos.show', $this->pedido->id),
            'tipo'    => 'pago_confirmado',
        ];
    }
}
