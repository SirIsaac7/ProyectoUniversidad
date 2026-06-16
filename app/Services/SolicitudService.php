<?php

namespace App\Services;

use App\Models\PerfilProveedor;
use App\Models\ProveedorEspecialidad;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Str;

class SolicitudService
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService,
        protected NotificacionService $notificacionService
    ) {
    }

    public function datosFormulario(): array
    {
        $proveedorEspecialidades = ProveedorEspecialidad::query()
            ->with([
                'especialidad:id,rubro_tipo_servicio_id,nombre,estado',
                'especialidad.rubroTipoServicio:id,rubro_id,tipo_servicio_id',
                'especialidad.rubroTipoServicio.rubro:id,nombre',
                'especialidad.rubroTipoServicio.tipoServicio:id,nombre',
            ])
            ->where('estado', true)
            ->whereHas('especialidad', fn ($query) => $query->where('estado', true))
            ->get(['perfil_proveedor_id', 'especialidad_id']);

        return [
            'clientes' => User::query()
                ->where('estado', true)
                ->whereHas('roles', fn ($query) => $query->where('name', 'CLIENTE'))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'perfilesProveedores' => PerfilProveedor::query()
                ->with('user:id,name,email')
                ->where('estado', true)
                ->whereHas('user', fn ($query) => $query->where('estado', true))
                ->orderBy('nombre_publico')
                ->get(['id', 'user_id', 'nombre_publico']),
            'especialidades' => $this->especialidadesFormulario($proveedorEspecialidades),
            'tiposAtencion' => [
                'mixto' => 'Mixto',
                'domicilio' => 'Domicilio',
                'local' => 'En local',
                'remoto' => 'Remoto',
            ],
            'estadosSolicitud' => [
                'pendiente' => 'Pendiente',
                'aceptada' => 'Aceptada',
                'rechazada' => 'Rechazada',
                'cancelada' => 'Cancelada',
                'en_proceso' => 'En proceso',
                'finalizada' => 'Finalizada',
            ],
        ];
    }

    public function createDesdeCliente(User $cliente, array $data): Solicitud
    {
        $solicitud = Solicitud::create([
            'cliente_user_id' => $cliente->id,
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'especialidad_id' => $data['especialidad_id'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'tipo_atencion' => $data['tipo_atencion'],
            'direccion' => $data['direccion'] ?? null,
            'zona' => $data['zona'] ?? null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'fecha_solicitada' => $data['fecha_solicitada'] ?? null,
            'hora_solicitada' => $data['hora_solicitada'] ?? null,
            'estado' => 'pendiente',
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        $this->historialSolicitudService->registrar(
            $solicitud,
            null,
            'pendiente',
            'Solicitud creada por el cliente'
        );

        $solicitud->load('perfilProveedor.user');

        if ($solicitud->perfilProveedor?->user) {
            $this->notificacionService->nuevaSolicitudParaProveedor(
                proveedor: $solicitud->perfilProveedor->user,
                cliente: $cliente->name,
                url: route('proveedor.solicitudes.index')
            );
        }

        return $solicitud;
    }

    public function updateDesdeCliente(Solicitud $solicitud, array $data): Solicitud
    {
        $solicitud->update([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'especialidad_id' => $data['especialidad_id'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'tipo_atencion' => $data['tipo_atencion'],
            'direccion' => $data['direccion'] ?? null,
            'zona' => $data['zona'] ?? null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'fecha_solicitada' => $data['fecha_solicitada'] ?? null,
            'hora_solicitada' => $data['hora_solicitada'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return $solicitud;
    }

    public function cancelar(Solicitud $solicitud): Solicitud
    {
        if (in_array($solicitud->estado, ['cancelada', 'finalizada'], true)) {
            return $solicitud;
        }

        $estadoAnterior = $solicitud->estado;

        $solicitud->update([
            'estado' => 'cancelada',
        ]);

        $this->historialSolicitudService->registrar(
            $solicitud,
            $estadoAnterior,
            'cancelada',
            'Solicitud cancelada'
        );

        return $solicitud;
    }

    public function cambiarEstadoDesdeProveedor(Solicitud $solicitud, string $estado, ?string $comentario = null): Solicitud
    {
        $estadoAnterior = $solicitud->estado;

        if ($estadoAnterior === $estado) {
            return $solicitud;
        }

        $solicitud->update([
            'estado' => $estado,
            'motivo_cancelacion' => $estado === 'rechazada' ? $comentario : $solicitud->motivo_cancelacion,
            'observaciones' => $comentario ?: $solicitud->observaciones,
        ]);

        $this->historialSolicitudService->registrar(
            $solicitud,
            $estadoAnterior,
            $estado,
            $comentario
        );

        $solicitud->load('cliente');

        if ($solicitud->cliente) {
            $this->notificacionService->solicitudActualizadaParaCliente(
                cliente: $solicitud->cliente,
                estado: $estado,
                url: route('cliente.solicitudes.index')
            );
        }

        return $solicitud;
    }

    public function datosFormularioCliente(): array
    {
        $datos = $this->datosFormulario();

        unset($datos['clientes'], $datos['estadosSolicitud']);

        return $datos;
    }

    public function solicitudesCliente(User $cliente)
    {
        return Solicitud::query()
            ->with([
                'perfilProveedor.user',
                'especialidad.rubroTipoServicio.rubro',
                'especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->where('cliente_user_id', $cliente->id)
            ->latest()
            ->paginate(10);
    }

    public function resumenSolicitudesCliente(User $cliente): array
    {
        $query = Solicitud::where('cliente_user_id', $cliente->id);

        return [
            'pendientes' => (clone $query)->where('estado', 'pendiente')->count(),
            'aceptadas' => (clone $query)->where('estado', 'aceptada')->count(),
            'enProceso' => (clone $query)->where('estado', 'en_proceso')->count(),
            'completadas' => (clone $query)->where('estado', 'finalizada')->count(),
        ];
    }

    public function estadosVistaCliente(): array
    {
        return [
            'pendiente' => ['label' => 'Pendiente', 'class' => 'warning', 'icon' => 'ri-time-line'],
            'aceptada' => ['label' => 'Aceptada', 'class' => 'success', 'icon' => 'ri-checkbox-circle-line'],
            'rechazada' => ['label' => 'Rechazada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
            'cancelada' => ['label' => 'Cancelada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
            'en_proceso' => ['label' => 'En proceso', 'class' => 'info', 'icon' => 'ri-loader-2-line'],
            'finalizada' => ['label' => 'Completada', 'class' => 'primary', 'icon' => 'ri-flag-line'],
        ];
    }

    public function estadisticasVistaCliente(array $resumenSolicitudes): array
    {
        return [
            [
                'titulo' => 'Pendientes',
                'valor' => $resumenSolicitudes['pendientes'],
                'texto' => 'En espera de respuesta',
                'icono' => 'ri-time-line',
                'color' => 'warning',
            ],
            [
                'titulo' => 'Aceptadas',
                'valor' => $resumenSolicitudes['aceptadas'],
                'texto' => 'Solicitud aceptada',
                'icono' => 'ri-calendar-check-line',
                'color' => 'info',
            ],
            [
                'titulo' => 'En proceso',
                'valor' => $resumenSolicitudes['enProceso'],
                'texto' => 'Servicio en curso',
                'icono' => 'ri-tools-line',
                'color' => 'success',
            ],
            [
                'titulo' => 'Completadas',
                'valor' => $resumenSolicitudes['completadas'],
                'texto' => 'Servicios finalizados',
                'icono' => 'ri-calendar-check-line',
                'color' => 'primary',
            ],
        ];
    }

    public function solicitudesVistaCliente($solicitudes, array $estadoMeta)
    {
        return $solicitudes->getCollection()
            ->map(function (Solicitud $solicitud) use ($estadoMeta) {
                $rubro = $solicitud->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro';
                $tipoServicio = $solicitud->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo';
                $especialidad = $solicitud->especialidad?->nombre ?? 'Sin especialidad';
                $proveedor = $solicitud->perfilProveedor?->nombre_publico ?? 'Sin proveedor';
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
                        $proveedor . ' ' .
                        $especialidad . ' ' .
                        $rubro . ' ' .
                        $tipoServicio . ' ' .
                        trim(($solicitud->zona ?: 'Sin zona') . ($solicitud->direccion ? ', ' . $solicitud->direccion : ''))
                    ),
                    'fecha_texto' => $fechaTexto,
                    'hora_texto' => $horaTexto,
                    'rubro' => $rubro,
                    'tipo_servicio' => $tipoServicio,
                    'especialidad' => $especialidad,
                    'proveedor' => $proveedor,
                    'zona' => $zona,
                    'direccion' => $direccion,
                    'tipo_atencion' => ucfirst(str_replace('_', ' ', $solicitud->tipo_atencion)),
                    'puede_editar' => $solicitud->estado === 'pendiente',
                    'puede_cancelar' => ! in_array($solicitud->estado, ['cancelada', 'finalizada'], true),
                ];
            });
    }

    public function detalleVistaCliente(?Solicitud $solicitud, array $estadoMeta): ?array
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
            'proveedor' => $solicitud->perfilProveedor?->nombre_publico ?? 'Sin proveedor',
            'fecha' => ($solicitud->fecha_solicitada?->format('d/m/Y') ?: 'Sin fecha')
                . ' - ' . ($solicitud->hora_solicitada?->format('H:i') ?: 'Sin hora'),
            'ubicacion' => ($solicitud->zona ?: 'Sin zona') . ' - ' . ($solicitud->direccion ?: 'Sin direccion'),
            'tipo_atencion' => ucfirst(str_replace('_', ' ', $solicitud->tipo_atencion)),
        ];
    }

    public function solicitudesProveedor(PerfilProveedor $perfilProveedor)
    {
        return Solicitud::query()
            ->with([
                'cliente',
                'especialidad.rubroTipoServicio.rubro',
                'especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->where('perfil_proveedor_id', $perfilProveedor->id)
            ->latest()
            ->paginate(10);
    }

    protected function especialidadesFormulario($proveedorEspecialidades)
    {
        return $proveedorEspecialidades
            ->groupBy('especialidad_id')
            ->map(function ($grupo) {
                $especialidad = $grupo->first()?->especialidad;

                if (! $especialidad) {
                    return null;
                }

                return [
                    'id' => $especialidad->id,
                    'nombre' => ($especialidad->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro')
                        . ' - ' . ($especialidad->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo')
                        . ' - ' . $especialidad->nombre,
                    'perfiles' => $grupo->pluck('perfil_proveedor_id')
                        ->unique()
                        ->map(fn ($id) => (string) $id)
                        ->values()
                        ->all(),
                ];
            })
            ->filter()
            ->values();
    }
}
