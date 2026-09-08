<?php
namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PagoConfirmadoCliente extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Pedido $pedido) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urlPedido = route('pedidos.confirmacion', $this->pedido->numero_pedido);

        return (new MailMessage)
            ->subject("✅ Pago confirmado — Pedido #{$this->pedido->numero_pedido}")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Confirmamos la recepción de tu pago para el pedido #{$this->pedido->numero_pedido}.")
            ->line("Total pagado: \${$this->pedido->total}")
            ->line("Ya comenzamos a preparar tu pedido.")
            ->action('Ver Pedido', $urlPedido)
            ->line("Gracias por comprar en Ambar.");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'titulo'  => 'Pago confirmado',
            'mensaje' => "Tu pago del pedido #{$this->pedido->numero_pedido} fue confirmado. Total: \${$this->pedido->total}.",
            'icono'   => 'fas fa-circle-dollar-to-slot',
            'color'   => 'success',
            'url'     => route('pedidos.confirmacion', $this->pedido->numero_pedido),
            'tipo'    => 'pago_confirmado',
        ];
    }
}
