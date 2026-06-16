<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\NotificacionSistema;

class NotificacionService
{
    public function enviar(
        User $usuario,
        string $titulo,
        string $mensaje,
        string $tipo = 'info',
        ?string $url = null,
        array $extra = []
    ): void {
        if (! $usuario->estado) {
            return;
        }

        $usuario->notify(new NotificacionSistema(
            titulo: $titulo,
            mensaje: $mensaje,
            tipo: $tipo,
            url: $url,
            extra: $extra
        ));
    }

    public function nuevaSolicitudParaProveedor(User $proveedor, string $cliente, ?string $url = null): void
    {
        $this->enviar(
            usuario: $proveedor,
            titulo: 'Nueva solicitud recibida',
            mensaje: $cliente . ' envio una nueva solicitud de servicio.',
            tipo: 'success',
            url: $url
        );
    }

    public function solicitudActualizadaParaCliente(User $cliente, string $estado, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Solicitud actualizada',
            mensaje: 'Tu solicitud fue actualizada al estado: ' . ucfirst(str_replace('_', ' ', $estado)) . '.',
            tipo: 'info',
            url: $url
        );
    }
}
