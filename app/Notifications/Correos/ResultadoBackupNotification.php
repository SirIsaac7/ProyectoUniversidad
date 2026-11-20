<?php

namespace App\Notifications\Correos;

use App\Services\Correos\ConfiguracionCorreoService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultadoBackupNotification extends Notification
{
    use Queueable;

    public function __construct(
        public bool $exitoso,
        public string $detalle,
        public string $fecha,
        public ?string $rutaArchivo = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        return app(ConfiguracionCorreoService::class)->correoResultadoBackup(
            $this->exitoso,
            $this->detalle,
            $this->fecha,
            $this->rutaArchivo
        );
    }
}
