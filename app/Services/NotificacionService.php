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

    public function citaProgramadaParaCliente(User $cliente, string $fecha, string $hora, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Cita programada',
            mensaje: 'Tu cita fue programada para el ' . $fecha . ' a las ' . $hora . '.',
            tipo: 'success',
            url: $url
        );
    }

    public function citaActualizadaParaCliente(User $cliente, string $estado, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Cita actualizada',
            mensaje: 'Tu cita fue actualizada al estado: ' . ucfirst(str_replace('_', ' ', $estado)) . '.',
            tipo: 'info',
            url: $url
        );
    }

    public function calificacionRecibidaParaProveedor(User $proveedor, string $cliente, ?string $url = null): void
    {
        $this->enviar(
            usuario: $proveedor,
            titulo: 'Nueva calificacion recibida',
            mensaje: $cliente . ' califico uno de tus servicios.',
            tipo: 'success',
            url: $url
        );
    }

    public function respuestaCalificacionParaCliente(User $cliente, string $proveedor, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Respuesta a tu calificacion',
            mensaje: $proveedor . ' respondio tu resena.',
            tipo: 'info',
            url: $url
        );
    }
}
