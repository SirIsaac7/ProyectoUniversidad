<?php

namespace App\Services;

use App\Models\PerfilProveedor;
use App\Models\ProveedorEspecialidad;
use App\Models\Solicitud;
use App\Models\User;

class SolicitudService
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService
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
