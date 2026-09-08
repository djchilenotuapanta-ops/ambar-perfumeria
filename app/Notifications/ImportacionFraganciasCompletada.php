<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ImportacionFraganciasCompletada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $nombreAdmin,
        public int $creadas,
        public int $actualizadas,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $partes = [];
        if ($this->creadas > 0) $partes[] = "{$this->creadas} nueva(s)";
        if ($this->actualizadas > 0) $partes[] = "{$this->actualizadas} actualizada(s)";
        $resumen = implode(' y ', $partes);

        return [
            'titulo'  => 'Carga masiva de fragancias',
            'mensaje' => "{$this->nombreAdmin} importó un catálogo: {$resumen}.",
            'icono'   => 'fas fa-file-csv',
            'color'   => 'info',
            'url'     => route('admin.fragancias.index'),
            'tipo'    => 'importacion_fragancias',
        ];
    }
}
