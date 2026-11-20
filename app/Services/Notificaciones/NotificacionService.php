<?php

namespace App\Services\Notificaciones;

use App\Models\User;
use App\Notifications\NotificacionSistema;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionService
{
    public const TIPOS = [
        'info' => ['nombre' => 'Informativas', 'color' => 'info', 'icono' => 'ri-information-line'],
        'success' => ['nombre' => 'Confirmaciones', 'color' => 'success', 'icono' => 'ri-checkbox-circle-line'],
        'warning' => ['nombre' => 'Advertencias', 'color' => 'warning', 'icono' => 'ri-alert-line'],
        'danger' => ['nombre' => 'Importantes', 'color' => 'danger', 'icono' => 'ri-error-warning-line'],
        'error' => ['nombre' => 'Errores', 'color' => 'danger', 'icono' => 'ri-close-circle-line'],
    ];

    public function centro(User $usuario, array $filtros = []): array
    {
        $consulta = $usuario->notifications();
        $estado = $filtros['estado'] ?? 'todas';
        $tipo = $filtros['tipo'] ?? 'todos';
        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        $consulta
            ->when($estado === 'no_leidas', fn ($query) => $query->whereNull('read_at'))
            ->when($estado === 'leidas', fn ($query) => $query->whereNotNull('read_at'))
            ->when($tipo !== 'todos', fn ($query) => $query->where('data->tipo', $tipo))
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where('data', 'like', '%'.$this->escaparLike($buscar).'%');
            });

        if (($filtros['orden'] ?? 'recientes') === 'antiguas') {
            $consulta->oldest();
        } else {
            $consulta->latest();
        }

        $notificaciones = $consulta
            ->paginate((int) ($filtros['por_pagina'] ?? 10))
            ->withQueryString();

        return [
            'notificaciones' => $notificaciones,
            'resumen' => $this->resumen($usuario),
            'tipos' => self::TIPOS,
        ];
    }

    public function resumen(User $usuario): array
    {
        $total = $usuario->notifications()->count();
        $noLeidas = $usuario->unreadNotifications()->count();
        $porTipo = [];

        foreach (array_keys(self::TIPOS) as $tipo) {
            $porTipo[$tipo] = $usuario->notifications()->where('data->tipo', $tipo)->count();
        }

        return [
            'total' => $total,
            'no_leidas' => $noLeidas,
            'leidas' => $total - $noLeidas,
            'por_tipo' => $porTipo,
        ];
    }

    public function propiasRecientes(User $usuario, int $limite = 10)
    {
        return $usuario->notifications()->latest()->limit($limite)->get();
    }

    public function buscarPropia(User $usuario, string $id): DatabaseNotification
    {
        return $usuario->notifications()->whereKey($id)->firstOrFail();
    }

    public function marcarLeida(User $usuario, string $id): void
    {
        $this->buscarPropia($usuario, $id)->markAsRead();
    }

    public function marcarNoLeida(User $usuario, string $id): void
    {
        $this->buscarPropia($usuario, $id)->markAsUnread();
    }

    public function marcarTodasLeidas(User $usuario): int
    {
        return $usuario->unreadNotifications()->update(['read_at' => now()]);
    }

    public function eliminar(User $usuario, string $id): void
    {
        $this->buscarPropia($usuario, $id)->delete();
    }

    public function ejecutarAccionMasiva(User $usuario, array $ids, string $accion): int
    {
        $consulta = $usuario->notifications()->whereIn('id', $ids);

        return match ($accion) {
            'leer' => $consulta->update(['read_at' => now()]),
            'no_leer' => $consulta->update(['read_at' => null]),
            'eliminar' => $consulta->delete(),
        };
    }

    private function escaparLike(string $valor): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $valor);
    }

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
            mensaje: $cliente.' envio una nueva solicitud de servicio.',
            tipo: 'success',
            url: $url
        );
    }

    public function solicitudActualizadaParaCliente(User $cliente, string $estado, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Solicitud actualizada',
            mensaje: 'Tu solicitud fue actualizada al estado: '.ucfirst(str_replace('_', ' ', $estado)).'.',
            tipo: 'info',
            url: $url
        );
    }

    public function citaProgramadaParaCliente(User $cliente, string $fecha, string $hora, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Cita programada',
            mensaje: 'Tu cita fue programada para el '.$fecha.' a las '.$hora.'.',
            tipo: 'success',
            url: $url
        );
    }

    public function citaActualizadaParaCliente(User $cliente, string $estado, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Cita actualizada',
            mensaje: 'Tu cita fue actualizada al estado: '.ucfirst(str_replace('_', ' ', $estado)).'.',
            tipo: 'info',
            url: $url
        );
    }

    public function calificacionRecibidaParaProveedor(User $proveedor, string $cliente, ?string $url = null): void
    {
        $this->enviar(
            usuario: $proveedor,
            titulo: 'Nueva calificacion recibida',
            mensaje: $cliente.' califico uno de tus servicios.',
            tipo: 'success',
            url: $url
        );
    }

    public function respuestaCalificacionParaCliente(User $cliente, string $proveedor, ?string $url = null): void
    {
        $this->enviar(
            usuario: $cliente,
            titulo: 'Respuesta a tu calificacion',
            mensaje: $proveedor.' respondio tu resena.',
            tipo: 'info',
            url: $url
        );
    }

    public function documentoPendienteParaAdministradores(
        string $proveedor,
        string $tipoDocumento,
        bool $actualizado = false,
        ?string $url = null
    ): void {
        User::role('ADMINISTRADOR')
            ->where('estado', true)
            ->each(function (User $administrador) use ($proveedor, $tipoDocumento, $actualizado, $url) {
                $this->enviar(
                    usuario: $administrador,
                    titulo: $actualizado ? 'Documento actualizado' : 'Nuevo documento por revisar',
                    mensaje: $proveedor.' '.($actualizado ? 'actualizo' : 'subio').' el documento '.$tipoDocumento.'.',
                    tipo: 'warning',
                    url: $url,
                    extra: ['modulo' => 'documentos_proveedor']
                );
            });
    }

    public function documentoRevisadoParaProveedor(
        User $proveedor,
        string $tipoDocumento,
        string $estado,
        ?string $url = null
    ): void {
        $this->enviar(
            usuario: $proveedor,
            titulo: 'Documento revisado',
            mensaje: 'Tu documento '.$tipoDocumento.' fue marcado como '.str_replace('_', ' ', $estado).'.',
            tipo: $estado === 'aprobado' ? 'success' : 'warning',
            url: $url,
            extra: ['modulo' => 'documentos_proveedor', 'estado' => $estado]
        );
    }

    public function perfilVerificadoParaProveedor(
        User $proveedor,
        string $estado,
        ?string $url = null
    ): void {
        $this->enviar(
            usuario: $proveedor,
            titulo: 'Verificacion de perfil actualizada',
            mensaje: 'El estado de verificacion de tu perfil cambio a '.str_replace('_', ' ', $estado).'.',
            tipo: $estado === 'aprobado' ? 'success' : ($estado === 'rechazado' ? 'warning' : 'info'),
            url: $url,
            extra: ['modulo' => 'perfil_proveedor', 'estado' => $estado]
        );
    }
}
