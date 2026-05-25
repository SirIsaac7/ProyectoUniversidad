<?php

namespace App\Services\Inicio;

use App\Models\PerfilProveedor;
use Spatie\Activitylog\Models\Activity;

class InicioService
{
    public function getData(): array
    {
        $user = auth()->user();

        $perfilProveedor = PerfilProveedor::with([
            'proveedorEspecialidades' => fn ($query) => $query->where('estado', true),
            'proveedorEspecialidades.especialidad',
            'horarios' => fn ($query) => $query->where('estado', true),
            'ubicacion',
            'portafolio' => fn ($query) => $query->where('estado', true),
            'documentos' => fn ($query) => $query->where('estado', true),
            'documentos.tipoDocumentoProveedor',
        ])
            ->where('user_id', $user->id)
            ->first();

        if ($perfilProveedor && $user->can('visualizar perfil proveedor')) {
            return [
                'tipoInicio' => 'proveedor',
                'inicioProveedor' => $this->getProveedorData($perfilProveedor),
            ];
        }

        return [
            'tipoInicio' => 'general',
            'inicioProveedor' => null,
        ];
    }

    private function getProveedorData(PerfilProveedor $perfilProveedor): array
    {
        $documentosAprobados = $perfilProveedor->documentos
            ->where('estado_revision', 'aprobado')
            ->count();

        $documentosPendientes = $perfilProveedor->documentos
            ->where('estado_revision', 'pendiente')
            ->count();

        $documentosRechazados = $perfilProveedor->documentos
            ->where('estado_revision', 'rechazado')
            ->count();

        return [
            'perfil' => $perfilProveedor,
            'perfilCompleto' => $this->calcularPerfilCompleto($perfilProveedor),
            'especialidadesActivas' => $perfilProveedor->proveedorEspecialidades->count(),
            'horariosDisponibles' => $perfilProveedor->horarios->where('disponible', true)->count(),
            'trabajosPortafolio' => $perfilProveedor->portafolio->count(),
            'documentosAprobados' => $documentosAprobados,
            'documentosPendientes' => $documentosPendientes,
            'documentosRechazados' => $documentosRechazados,
            'tieneUbicacion' => (bool) $perfilProveedor->ubicacion,
            'actividadReciente' => $this->getActividadReciente(),
        ];
    }

    private function calcularPerfilCompleto(PerfilProveedor $perfilProveedor): int
    {
        $items = [
            filled($perfilProveedor->nombre_publico),
            filled($perfilProveedor->descripcion),
            filled($perfilProveedor->foto_portada),
            filled($perfilProveedor->anios_experiencia),
            $perfilProveedor->proveedorEspecialidades->isNotEmpty(),
            $perfilProveedor->horarios->isNotEmpty(),
            (bool) $perfilProveedor->ubicacion,
            $perfilProveedor->portafolio->isNotEmpty(),
            $perfilProveedor->documentos->where('estado_revision', 'aprobado')->isNotEmpty(),
        ];

        $completados = collect($items)->filter()->count();

        return (int) round(($completados / count($items)) * 100);
    }

    private function getActividadReciente()
    {
        return Activity::query()
            ->where('causer_id', auth()->id())
            ->latest()
            ->take(4)
            ->get();
    }
}
