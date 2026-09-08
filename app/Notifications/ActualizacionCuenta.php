<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActualizacionCuenta extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $camposCambiados) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lista = implode(', ', $this->camposCambiados);

        return (new MailMessage)
            ->subject('Tus datos de cuenta fueron actualizados')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Se actualizaron los siguientes datos de tu cuenta: {$lista}.")
            ->line('Si no realizaste este cambio, contáctanos de inmediato.')
            ->action('Ver mi perfil', route('profile.edit'));
    }

    public function toDatabase(object $notifiable): array
    {
        $lista = implode(', ', $this->camposCambiados);

        return [
            'titulo'  => 'Datos de cuenta actualizados',
            'mensaje' => "Se actualizaron: {$lista}.",
            'icono'   => 'fas fa-user-pen',
            'color'   => 'info',
            'url'     => route('profile.edit'),
            'tipo'    => 'cuenta_actualizada',
        ];
    }
}
