<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\PerfilProveedor;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CitaService
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService,
        protected NotificacionService $notificacionService
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
            ->paginate(10, ['*'], 'citas_page');
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
            ->paginate(10, ['*'], 'citas_page');
    }

    public function estadosVistaCliente(): array
    {
        return [
            'programada' => ['label' => 'Programada', 'class' => 'primary', 'icon' => 'ri-calendar-check-line'],
            'en_atencion' => ['label' => 'En atencion', 'class' => 'warning', 'icon' => 'ri-tools-line'],
            'completada' => ['label' => 'Completada', 'class' => 'success', 'icon' => 'ri-checkbox-circle-line'],
            'cancelada' => ['label' => 'Cancelada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
            'no_asistio' => ['label' => 'No asistio', 'class' => 'danger', 'icon' => 'ri-user-unfollow-line'],
            'vencida' => ['label' => 'Vencida', 'class' => 'secondary', 'icon' => 'ri-timer-flash-line'],
        ];
    }

    public function citasVistaCliente($citas, array $estadoCitaMeta)
    {
        return $citas->getCollection()
            ->map(function (Cita $cita) use ($estadoCitaMeta) {
                $solicitud = $cita->solicitud;

                return [
                    'id' => $cita->id,
                    'titulo' => $solicitud?->titulo ?? 'Solicitud sin titulo',
                    'rubro' => $solicitud?->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro',
                    'especialidad' => $solicitud?->especialidad?->nombre ?? 'Sin especialidad',
                    'proveedor' => $solicitud?->perfilProveedor?->nombre_publico ?? 'Sin proveedor',
                    'proveedor_avatar' => $solicitud?->perfilProveedor?->user?->avatar_url,
                    'proveedor_inicial' => $solicitud?->perfilProveedor?->user?->inicial
                        ?? mb_strtoupper(mb_substr($solicitud?->perfilProveedor?->nombre_publico ?? 'P', 0, 1)),
                    'fecha' => $cita->fecha_cita?->format('d/m/Y') ?? 'Sin fecha',
                    'hora_inicio' => $cita->hora_inicio?->format('H:i') ?? '--:--',
                    'hora_fin' => $cita->hora_fin?->format('H:i') ?? '--:--',
                    'observaciones' => $cita->observaciones ?: 'Sin observaciones',
                    'meta' => $estadoCitaMeta[$cita->estado] ?? [
                        'label' => ucfirst(str_replace('_', ' ', $cita->estado)),
                        'class' => 'secondary',
                        'icon' => 'ri-information-line',
                    ],
                ];
            });
    }

    public function citasVistaProveedor($citas, array $estadoCitaMeta)
    {
        return $citas->getCollection()
            ->map(function (Cita $cita) use ($estadoCitaMeta) {
                $solicitud = $cita->solicitud;

                return [
                    'id' => $cita->id,
                    'titulo' => $solicitud?->titulo ?? 'Solicitud sin titulo',
                    'rubro' => $solicitud?->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro',
                    'especialidad' => $solicitud?->especialidad?->nombre ?? 'Sin especialidad',
                    'cliente' => $solicitud?->cliente?->name ?? 'Sin cliente',
                    'cliente_avatar' => $solicitud?->cliente?->avatar_url,
                    'cliente_inicial' => $solicitud?->cliente?->inicial ?? 'C',
                    'fecha' => $cita->fecha_cita?->format('d/m/Y') ?? 'Sin fecha',
                    'hora_inicio' => $cita->hora_inicio?->format('H:i') ?? '--:--',
                    'hora_fin' => $cita->hora_fin?->format('H:i') ?? '--:--',
                    'observaciones' => $cita->observaciones ?: 'Sin observaciones',
                    'meta' => $estadoCitaMeta[$cita->estado] ?? [
                        'label' => ucfirst(str_replace('_', ' ', $cita->estado)),
                        'class' => 'secondary',
                        'icon' => 'ri-information-line',
                    ],
                    'puede_iniciar' => $this->puedeIniciarAtencion($cita),
                    'puede_completar' => $cita->estado === 'en_atencion',
                    'puede_cancelar' => ! in_array($cita->estado, ['completada', 'cancelada', 'no_asistio', 'vencida'], true),
                ];
            });
    }

    public function crearDesdeProveedor(Solicitud $solicitud, array $data): Cita
    {
        return DB::transaction(function () use ($solicitud, $data) {
            $estadoAnterior = $solicitud->estado;

            if ($solicitud->estado !== 'aceptada') {
                $solicitud->update(['estado' => 'aceptada']);

                $this->historialSolicitudService->registrar(
                    $solicitud,
                    $estadoAnterior,
                    'aceptada',
                    'Solicitud aceptada al programar una cita'
                );
            }

            $cita = Cita::create([
                'solicitud_id' => $solicitud->id,
                'fecha_cita' => $data['fecha_cita'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'estado' => 'programada',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $this->registrarEventoSolicitud($solicitud, 'Cita programada por el proveedor');

            $solicitud->load('cliente');

            if ($solicitud->cliente) {
                $this->notificacionService->citaProgramadaParaCliente(
                    cliente: $solicitud->cliente,
                    fecha: $cita->fecha_cita?->format('d/m/Y') ?? $data['fecha_cita'],
                    hora: $cita->hora_inicio?->format('H:i') ?? $data['hora_inicio'],
                    url: route('cliente.solicitudes.index', ['tab' => 'citas'])
                );
            }

            return $cita;
        });
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

        $cita->load('solicitud.cliente');

        if ($cita->solicitud?->cliente) {
            $this->notificacionService->citaActualizadaParaCliente(
                cliente: $cita->solicitud->cliente,
                estado: $nuevoEstado,
                url: route('cliente.solicitudes.index', ['tab' => 'citas'])
            );
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

        $cita->load('solicitud.cliente');

        if ($cita->solicitud?->cliente) {
            $this->notificacionService->citaActualizadaParaCliente(
                cliente: $cita->solicitud->cliente,
                estado: 'cancelada',
                url: route('cliente.solicitudes.index', ['tab' => 'citas'])
            );
        }

        return $cita;
    }

    protected function sincronizarSolicitudPorEstadoCita(
        Solicitud $solicitud,
        string $estadoCita,
        ?string $comentario = null
    ): void {
        $nuevoEstadoSolicitud = match ($estadoCita) {
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

    protected function puedeIniciarAtencion(Cita $cita): bool
    {
        if ($cita->estado !== 'programada' || ! $cita->fecha_cita || ! $cita->hora_inicio) {
            return false;
        }

        $inicio = Carbon::parse($cita->fecha_cita->format('Y-m-d') . ' ' . $cita->hora_inicio->format('H:i'));

        return now()->isSameDay($inicio) && now()->greaterThanOrEqualTo($inicio->copy()->subMinutes(15));
    }
}
