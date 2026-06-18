<?php

namespace App\Services;

use App\Models\HistorialSolicitud;
use App\Models\PerfilProveedor;
use App\Models\Solicitud;
use App\Models\User;

class HistorialSolicitudService
{
    public function registrar(
        Solicitud $solicitud,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $comentario = null,
        ?int $userId = null
    ): HistorialSolicitud {
        return HistorialSolicitud::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $userId ?? auth()->id(),
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'comentario' => $comentario,
        ]);
    }

    public function historialSolicitud(Solicitud $solicitud)
    {
        return HistorialSolicitud::query()
            ->with('user:id,name,email')
            ->where('solicitud_id', $solicitud->id)
            ->latest()
            ->get();
    }

    public function historialCliente(User $cliente)
    {
        return HistorialSolicitud::query()
            ->with(['user:id,name,email', 'solicitud.perfilProveedor'])
            ->whereHas('solicitud', fn ($query) => $query->where('cliente_user_id', $cliente->id))
            ->latest()
            ->paginate(10, ['*'], 'historial_page');
    }

    public function historialProveedor(PerfilProveedor $perfilProveedor)
    {
        return HistorialSolicitud::query()
            ->with(['user:id,name,email', 'solicitud.cliente'])
            ->whereHas('solicitud', fn ($query) => $query->where('perfil_proveedor_id', $perfilProveedor->id))
            ->latest()
            ->paginate(10, ['*'], 'historial_page');
    }

    public function historialGeneral()
    {
        return HistorialSolicitud::query()
            ->with(['user:id,name,email', 'solicitud.cliente', 'solicitud.perfilProveedor'])
            ->latest()
            ->paginate(15);
    }

    public function historialVistaCliente($historiales)
    {
        return $historiales->getCollection()
            ->map(function (HistorialSolicitud $historial) {
                return [
                    'solicitud' => $historial->solicitud?->titulo ?? 'Sin solicitud',
                    'proveedor' => $historial->solicitud?->perfilProveedor?->nombre_publico ?? 'Sin proveedor',
                    'estado_anterior' => $historial->estado_anterior
                        ? ucfirst(str_replace('_', ' ', $historial->estado_anterior))
                        : 'Sin estado',
                    'estado_nuevo' => ucfirst(str_replace('_', ' ', $historial->estado_nuevo)),
                    'usuario' => $historial->user?->name ?? 'Sistema',
                    'email' => $historial->user?->email,
                    'comentario' => $historial->comentario ?: 'Sin comentario',
                    'fecha' => optional($historial->created_at)->format('d/m/Y H:i'),
                ];
            });
    }

    public function historialVistaProveedor($historiales)
    {
        return $historiales->getCollection()
            ->map(function (HistorialSolicitud $historial) {
                return [
                    'solicitud' => $historial->solicitud?->titulo ?? 'Sin solicitud',
                    'cliente' => $historial->solicitud?->cliente?->name ?? 'Sin cliente',
                    'estado_anterior' => $historial->estado_anterior
                        ? ucfirst(str_replace('_', ' ', $historial->estado_anterior))
                        : 'Sin estado',
                    'estado_nuevo' => ucfirst(str_replace('_', ' ', $historial->estado_nuevo)),
                    'usuario' => $historial->user?->name ?? 'Sistema',
                    'email' => $historial->user?->email,
                    'comentario' => $historial->comentario ?: 'Sin comentario',
                    'fecha' => optional($historial->created_at)->format('d/m/Y H:i'),
                ];
            });
    }
}
