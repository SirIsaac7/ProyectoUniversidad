<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\PerfilProveedor;
use App\Models\Solicitud;
use App\Models\User;

class CitaService
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService
    ) {
    }

    public function citasAdmin()
    {
        return Cita::query()
            ->with([
                'solicitud.cliente',
                'solicitud.perfilProveedor',
                'solicitud.especialidad',
            ])
            ->latest()
            ->paginate(15);
    }

    public function citasCliente(User $cliente)
    {
        return Cita::query()
            ->with([
                'solicitud.perfilProveedor',
                'solicitud.especialidad.rubroTipoServicio.rubro',
                'solicitud.especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->whereHas('solicitud', fn ($query) => $query->where('cliente_user_id', $cliente->id))
            ->latest()
            ->paginate(10);
    }

    public function citasProveedor(PerfilProveedor $perfilProveedor)
    {
        return Cita::query()
            ->with([
                'solicitud.cliente',
                'solicitud.especialidad.rubroTipoServicio.rubro',
                'solicitud.especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->whereHas('solicitud', fn ($query) => $query->where('perfil_proveedor_id', $perfilProveedor->id))
            ->latest()
            ->paginate(10);
    }

    public function crearDesdeProveedor(Solicitud $solicitud, array $data): Cita
    {
        $cita = Cita::create([
            'solicitud_id' => $solicitud->id,
            'fecha_cita' => $data['fecha_cita'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'estado' => 'programada',
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        $this->registrarEventoSolicitud($solicitud, 'Cita programada por el proveedor');

        return $cita;
    }

    public function actualizarDesdeProveedor(Cita $cita, array $data): Cita
    {
        $estadoAnterior = $cita->estado;
        $nuevoEstado = $data['estado'] ?? $cita->estado;

        $cita->update([
            'fecha_cita' => $data['fecha_cita'] ?? $cita->fecha_cita?->format('Y-m-d'),
            'hora_inicio' => $data['hora_inicio'] ?? $cita->hora_inicio?->format('H:i'),
            'hora_fin' => $data['hora_fin'] ?? $cita->hora_fin?->format('H:i'),
            'estado' => $nuevoEstado,
            'observaciones' => $data['observaciones'] ?? $cita->observaciones,
        ]);

        if ($estadoAnterior !== $nuevoEstado) {
            $this->sincronizarSolicitudPorEstadoCita(
                $cita->solicitud,
                $nuevoEstado,
                $data['observaciones'] ?? null
            );

            if (! in_array($nuevoEstado, ['en_atencion', 'completada'], true)) {
                $this->registrarEventoSolicitud(
                    $cita->solicitud,
                    'Estado de cita actualizado a ' . str_replace('_', ' ', $nuevoEstado)
                );
            }
        } elseif (array_key_exists('fecha_cita', $data) || array_key_exists('hora_inicio', $data) || array_key_exists('hora_fin', $data)) {
            $this->registrarEventoSolicitud($cita->solicitud, 'Cita actualizada o reprogramada');
        }

        return $cita;
    }

    public function cambiarEstado(Cita $cita, string $estado, ?string $observaciones = null): Cita
    {
        return $this->actualizarDesdeProveedor($cita, [
            'estado' => $estado,
            'observaciones' => $observaciones,
        ]);
    }

    public function cancelar(Cita $cita, ?string $observaciones = null): Cita
    {
        $cita->update([
            'estado' => 'cancelada',
            'observaciones' => $observaciones ?: $cita->observaciones,
        ]);

        $this->historialSolicitudService->registrar(
            $cita->solicitud,
            $cita->solicitud->estado,
            $cita->solicitud->estado,
            $observaciones ?: 'Cita cancelada'
        );

        return $cita;
    }

    protected function sincronizarSolicitudPorEstadoCita(
        Solicitud $solicitud,
        string $estadoCita,
        ?string $comentario = null
    ): void {
        $nuevoEstadoSolicitud = match ($estadoCita) {
            'en_atencion' => 'en_proceso',
            'completada' => 'finalizada',
            default => null,
        };

        if (! $nuevoEstadoSolicitud || $solicitud->estado === $nuevoEstadoSolicitud) {
            return;
        }

        $this->actualizarEstadoSolicitud($solicitud, $nuevoEstadoSolicitud, $comentario);
    }

    protected function actualizarEstadoSolicitud(
        Solicitud $solicitud,
        string $estado,
        ?string $comentario = null
    ): void {
        $estadoAnterior = $solicitud->estado;

        if ($estadoAnterior === $estado) {
            return;
        }

        $solicitud->update([
            'estado' => $estado,
        ]);

        $this->historialSolicitudService->registrar(
            $solicitud,
            $estadoAnterior,
            $estado,
            $comentario
        );
    }

    protected function registrarEventoSolicitud(Solicitud $solicitud, string $comentario): void
    {
        $this->historialSolicitudService->registrar(
            $solicitud,
            $solicitud->estado,
            $solicitud->estado,
            $comentario
        );
    }
}
