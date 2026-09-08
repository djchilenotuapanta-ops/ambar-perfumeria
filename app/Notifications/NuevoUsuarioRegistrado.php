<?php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevoUsuarioRegistrado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $usuario) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("👤 Nuevo cliente registrado")
            ->greeting("Nuevo registro")
            ->line("Se registró un nuevo cliente en Ambar.")
            ->line("Nombre: {$this->usuario->name}")
            ->line("Correo: {$this->usuario->email}")
            ->action('Ver Usuarios', route('admin.usuarios.index'))
            ->line("Ambar - Sistema de Gestión");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'usuario_id' => $this->usuario->id,
            'titulo'  => 'Nuevo cliente registrado',
            'mensaje' => "{$this->usuario->name} ({$this->usuario->email}) se registró en la plataforma.",
            'icono'   => 'fas fa-user-plus',
            'color'   => 'info',
            'url'     => route('admin.usuarios.index'),
            'tipo'    => 'usuario_nuevo',
        ];
    }
}
