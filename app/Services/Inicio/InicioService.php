<?php

namespace App\Services\Inicio;

use App\Models\Cita;
use App\Models\PerfilProveedor;
use App\Services\CalificacionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class InicioService
{
    public function __construct(
        protected CalificacionService $calificacionService
    ) {
    }

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

        if ($perfilProveedor && Gate::allows('view-inicio', 'proveedor')) {
            return [
                'tipoInicio' => 'proveedor',
                'inicioProveedor' => $this->getProveedorData($perfilProveedor),
                'inicioAdmin' => null,
                'inicioCliente' => null,
            ];
        }

        if (Gate::allows('view-inicio', 'admin')) {
            return [
                'tipoInicio' => 'admin',
                'inicioProveedor' => null,
                'inicioAdmin' => [],
                'inicioCliente' => null,
            ];
        }

        if (Gate::allows('view-inicio', 'cliente')) {
            return [
                'tipoInicio' => 'cliente',
                'inicioProveedor' => null,
                'inicioAdmin' => null,
                'inicioCliente' => [],
            ];
        }

        return [
            'tipoInicio' => 'general',
            'inicioProveedor' => null,
            'inicioAdmin' => null,
            'inicioCliente' => null,
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
            'resumenCitas' => $this->getResumenCitas($perfilProveedor),
            'resumenCalificaciones' => $this->calificacionService->resumenProveedor($perfilProveedor->user),
            'actividadReciente' => $this->getActividadReciente(),
        ];
    }

    private function getResumenCitas(PerfilProveedor $perfilProveedor): array
    {
        $inicioAnio = now()->startOfYear()->toDateString();
        $finAnio = now()->endOfYear()->toDateString();

        $query = Cita::query()
            ->whereHas('solicitud', function ($query) use ($perfilProveedor) {
                $query->where('perfil_proveedor_id', $perfilProveedor->id);
            });

        $queryAnio = (clone $query)->whereBetween('fecha_cita', [$inicioAnio, $finAnio]);

        $mensuales = (clone $queryAnio)
            ->selectRaw('MONTH(fecha_cita) as mes')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas")
            ->selectRaw("SUM(CASE WHEN estado IN ('cancelada', 'no_asistio', 'vencida') THEN 1 ELSE 0 END) as incidencias")
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        $meses = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        $labels = [];
        $totales = [];
        $completadas = [];
        $incidencias = [];

        foreach ($meses as $numeroMes => $label) {
            $registro = $mensuales->get($numeroMes);

            $labels[] = $label;
            $totales[] = (int) ($registro->total ?? 0);
            $completadas[] = (int) ($registro->completadas ?? 0);
            $incidencias[] = (int) ($registro->incidencias ?? 0);
        }

        return [
            'anio' => Carbon::parse($inicioAnio)->year,
            'totalAnio' => (clone $queryAnio)->count(),
            'programadas' => (clone $queryAnio)->where('estado', 'programada')->count(),
            'enAtencion' => (clone $queryAnio)->where('estado', 'en_atencion')->count(),
            'completadas' => (clone $queryAnio)->where('estado', 'completada')->count(),
            'incidencias' => (clone $queryAnio)->whereIn('estado', ['cancelada', 'no_asistio', 'vencida'])->count(),
            'labels' => $labels,
            'series' => [
                'totales' => $totales,
                'completadas' => $completadas,
                'incidencias' => $incidencias,
            ],
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
