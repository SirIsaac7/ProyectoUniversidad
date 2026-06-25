<?php

namespace App\Services\Proveedor;

use App\Models\PerfilProveedor;
use App\Models\Solicitud;
use Illuminate\Support\Str;

class SolicitudProveedorService
{
    public function solicitudes(PerfilProveedor $perfilProveedor)
    {
        return Solicitud::query()
            ->with([
                'cliente:id,name,email',
                'cita:id,solicitud_id,estado',
                'especialidad.rubroTipoServicio.rubro',
                'especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->where('perfil_proveedor_id', $perfilProveedor->id)
            ->latest()
            ->paginate(10, ['*'], 'solicitudes_page');
    }

    public function resumenSolicitudes(PerfilProveedor $perfilProveedor): array
    {
        $query = Solicitud::where('perfil_proveedor_id', $perfilProveedor->id);

        return [
            'pendientes' => (clone $query)->where('estado', 'pendiente')->count(),
            'aceptadas' => (clone $query)->where('estado', 'aceptada')->count(),
            'rechazadas' => (clone $query)->where('estado', 'rechazada')->count(),
            'finalizadas' => (clone $query)->where('estado', 'finalizada')->count(),
        ];
    }

    public function estadosVista(): array
    {
        return [
            'pendiente' => ['label' => 'Pendiente', 'class' => 'warning', 'icon' => 'ri-time-line'],
            'aceptada' => ['label' => 'Aceptada', 'class' => 'success', 'icon' => 'ri-checkbox-circle-line'],
            'rechazada' => ['label' => 'Rechazada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
            'cancelada' => ['label' => 'Cancelada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
            'finalizada' => ['label' => 'Finalizada', 'class' => 'primary', 'icon' => 'ri-flag-line'],
        ];
    }

    public function estadisticasVista(array $resumenSolicitudes): array
    {
        return [
            [
                'titulo' => 'Pendientes',
                'valor' => $resumenSolicitudes['pendientes'],
                'texto' => 'Esperando tu respuesta',
                'icono' => 'ri-time-line',
                'color' => 'warning',
            ],
            [
                'titulo' => 'Aceptadas',
                'valor' => $resumenSolicitudes['aceptadas'],
                'texto' => 'Listas para coordinar',
                'icono' => 'ri-calendar-check-line',
                'color' => 'success',
            ],
            [
                'titulo' => 'Finalizadas',
                'valor' => $resumenSolicitudes['finalizadas'],
                'texto' => 'Servicios concluidos',
                'icono' => 'ri-flag-line',
                'color' => 'primary',
            ],
            [
                'titulo' => 'Rechazadas',
                'valor' => $resumenSolicitudes['rechazadas'] ?? 0,
                'texto' => 'No aceptadas',
                'icono' => 'ri-close-circle-line',
                'color' => 'danger',
            ],
        ];
    }

    public function solicitudesVista($solicitudes, array $estadoMeta)
    {
        return $solicitudes->getCollection()
            ->map(function (Solicitud $solicitud) use ($estadoMeta) {
                $rubro = $solicitud->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro';
                $tipoServicio = $solicitud->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo';
                $especialidad = $solicitud->especialidad?->nombre ?? 'Sin especialidad';
                $cliente = $solicitud->cliente?->name ?? 'Sin cliente';
                $clienteUsuario = $solicitud->cliente;
                $fechaTexto = $solicitud->fecha_solicitada ? $solicitud->fecha_solicitada->format('d/m/Y') : 'Sin fecha';
                $horaTexto = $solicitud->hora_solicitada?->format('H:i') ?: 'Sin hora';
                $zona = $solicitud->zona ?: 'Sin zona';
                $direccion = $solicitud->direccion ?: 'Sin direccion';
                $meta = $estadoMeta[$solicitud->estado] ?? [
                    'label' => ucfirst(str_replace('_', ' ', $solicitud->estado)),
                    'class' => 'secondary',
                    'icon' => 'ri-information-line',
                ];

                return [
                    'modelo' => $solicitud,
                    'id' => $solicitud->id,
                    'titulo' => $solicitud->titulo,
                    'descripcion' => $solicitud->descripcion,
                    'estado' => $solicitud->estado,
                    'meta' => $meta,
                    'created_timestamp' => optional($solicitud->created_at)->timestamp ?? 0,
                    'search' => Str::lower(
                        $solicitud->titulo . ' ' .
                        $solicitud->descripcion . ' ' .
                        $cliente . ' ' .
                        $especialidad . ' ' .
                        $rubro . ' ' .
                        $tipoServicio . ' ' .
                        $zona . ' ' .
                        $direccion
                    ),
                    'fecha_texto' => $fechaTexto,
                    'hora_texto' => $horaTexto,
                    'rubro' => $rubro,
                    'tipo_servicio' => $tipoServicio,
                    'especialidad' => $especialidad,
                    'cliente' => $cliente,
                    'cliente_avatar' => $clienteUsuario?->avatar_url,
                    'cliente_inicial' => $clienteUsuario?->inicial ?? mb_strtoupper(mb_substr($cliente, 0, 1)),
                    'cliente_email' => $solicitud->cliente?->email,
                    'zona' => $zona,
                    'direccion' => $direccion,
                    'tipo_atencion' => ucfirst(str_replace('_', ' ', $solicitud->tipo_atencion)),
                    'puede_agendar' => $solicitud->estado === 'pendiente' && ! $solicitud->cita,
                    'puede_rechazar' => $solicitud->estado === 'pendiente',
                ];
            });
    }

    public function detalleVista(?Solicitud $solicitud, array $estadoMeta): ?array
    {
        if (! $solicitud) {
            return null;
        }

        $meta = $estadoMeta[$solicitud->estado] ?? [
            'label' => ucfirst(str_replace('_', ' ', $solicitud->estado)),
            'class' => 'secondary',
            'icon' => 'ri-information-line',
        ];

        return [
            'id' => $solicitud->id,
            'titulo' => $solicitud->titulo,
            'descripcion' => $solicitud->descripcion,
            'meta' => $meta,
            'categoria' => ($solicitud->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro')
                . ' - ' . ($solicitud->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo'),
            'especialidad' => $solicitud->especialidad?->nombre ?? 'Sin especialidad',
            'cliente' => $solicitud->cliente?->name ?? 'Sin cliente',
            'cliente_avatar' => $solicitud->cliente?->avatar_url,
            'fecha' => ($solicitud->fecha_solicitada?->format('d/m/Y') ?: 'Sin fecha')
                . ' - ' . ($solicitud->hora_solicitada?->format('H:i') ?: 'Sin hora'),
            'ubicacion' => ($solicitud->zona ?: 'Sin zona') . ' - ' . ($solicitud->direccion ?: 'Sin direccion'),
            'tipo_atencion' => ucfirst(str_replace('_', ' ', $solicitud->tipo_atencion)),
        ];
    }
}
