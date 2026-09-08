<?php
namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PedidoCanceladoAdmin extends Notification implements ShouldQueue
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
            ->subject("❌ Pedido cancelado — #{$this->pedido->numero_pedido}")
            ->greeting("Pedido cancelado")
            ->line("El pedido #{$this->pedido->numero_pedido} fue cancelado.")
            ->line("Cliente: {$this->pedido->user->name} ({$this->pedido->user->email})")
            ->line("Total: \${$this->pedido->total}")
            ->action('Ver Pedido', $urlPedido)
            ->line("Ambar - Sistema de Gestión");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'titulo'  => 'Pedido cancelado',
            'mensaje' => "El pedido #{$this->pedido->numero_pedido} de {$this->pedido->user->name} fue cancelado.",
            'icono'   => 'fas fa-ban',
            'color'   => 'danger',
            'url'     => route('admin.pedidos.show', $this->pedido->id),
            'tipo'    => 'pedido_cancelado',
        ];
    }
}
