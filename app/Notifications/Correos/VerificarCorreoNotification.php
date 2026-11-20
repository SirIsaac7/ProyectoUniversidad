<?php

namespace App\Notifications\Correos;

use App\Services\Correos\ConfiguracionCorreoService;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerificarCorreoNotification extends VerifyEmail
{
    public function toMail($notifiable)
    {
        return app(ConfiguracionCorreoService::class)->correoVerificacion(
            $notifiable,
            $this->verificationUrl($notifiable)
        );
    }
}
