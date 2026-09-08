<?php
namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevoPedidoAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Pedido $pedido
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urlPedido = route('admin.pedidos.show', $this->pedido->id);
        $detallesCount = $this->pedido->detalles()->count();
        $totalItems = $this->pedido->detalles()->sum('cantidad');

        return (new MailMessage)
            ->subject("🛍️ Nuevo Pedido #{$this->pedido->numero_pedido}")
            ->greeting("Nuevo pedido registrado")
            ->line("Se ha recibido un nuevo pedido en Ambar.")
            ->line("")
            ->line("📊 **Información del Pedido:**")
            ->line("Número: {$this->pedido->numero_pedido}")
            ->line("Cliente: {$this->pedido->user->name} ({$this->pedido->user->email})")
            ->line("Total: \${$this->pedido->total}")
            ->line("Items: {$totalItems} fragancias")
            ->line("Método de Pago: {$this->pedido->pago_metodo}")
            ->line("Dirección: {$this->pedido->direccion_envio}, {$this->pedido->ciudad_envio}")
            ->line("")
            ->action('Ver Pedido', $urlPedido)
            ->line("Revisa los detalles y confirma el pago para proceder con la preparación.");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'numero_pedido' => $this->pedido->numero_pedido,
            'cliente' => $this->pedido->user->name,
            'total' => $this->pedido->total,
            'titulo'  => 'Nuevo pedido',
            'mensaje' => "Nuevo pedido #{$this->pedido->numero_pedido} de {$this->pedido->user->name} por \${$this->pedido->total}.",
            'icono'   => 'fas fa-shopping-bag',
            'color'   => 'primary',
            'url'     => route('admin.pedidos.show', $this->pedido->id),
            'tipo'    => 'pedido_nuevo',
        ];
    }
}
