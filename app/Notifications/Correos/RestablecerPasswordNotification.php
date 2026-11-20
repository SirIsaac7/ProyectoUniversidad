<?php

namespace App\Notifications\Correos;

use App\Services\Correos\ConfiguracionCorreoService;
use Illuminate\Auth\Notifications\ResetPassword;

class RestablecerPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        return app(ConfiguracionCorreoService::class)->correoRecuperacion(
            $notifiable,
            $this->resetUrl($notifiable)
        );
    }
}
