<?php

namespace App\Services\Inicio;

use App\Models\Cita;
use App\Models\Calificacion;
use App\Models\ConfiguracionBackup;
use App\Models\DetalleCalificacion;
use App\Models\DocumentoProveedor;
use App\Models\Especialidad;
use App\Models\HistorialSolicitud;
use App\Models\HorarioProveedor;
use App\Models\PerfilProveedor;
use App\Models\PortafolioProveedor;
use App\Models\PortafolioProveedorImagen;
use App\Models\ProveedorEspecialidad;
use App\Models\RespuestaCalificacion;
use App\Models\Rubro;
use App\Models\Solicitud;
use App\Models\TipoDocumentoProveedor;
use App\Models\TipoServicio;
use App\Models\UbicacionProveedor;
use App\Models\User;
use App\Services\CalificacionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
                'inicioAdmin' => $this->getAdminData(),
                'inicioCliente' => null,
            ];
        }

        if (Gate::allows('view-inicio', 'cliente')) {
            return [
                'tipoInicio' => 'cliente',
                'inicioProveedor' => null,
                'inicioAdmin' => null,
                'inicioCliente' => $this->getClienteData($user),
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

    private function getAdminData(): array
    {
        return [
            'resumen' => $this->getResumenAdmin(),
            'resumenCitas' => $this->getResumenCitasDesdeQuery(Cita::query()),
            'rendimientoCitas' => $this->getRendimientoCitasAdmin(),
            'modulos' => $this->getResumenModulosAdmin(),
            'solicitudesPorEstado' => $this->getSolicitudesPorEstadoAdmin(),
            'proveedoresPorVerificacion' => $this->getProveedoresPorVerificacionAdmin(),
            'especialidadesTop' => $this->getEspecialidadesTopAdmin(),
            'proveedoresMapa' => $this->getProveedoresMapaAdmin(),
            'backup' => $this->getBackupAdmin(),
            'evolutionApi' => $this->getEvolutionApiAdmin(),
            'busquedaInteligente' => $this->getBusquedaInteligenteAdmin(),
            'actividadReciente' => $this->getActividadRecienteAdmin(),
        ];
    }

    private function getResumenAdmin(): array
    {
        $totalCalificaciones = Calificacion::query()->where('estado', 'visible')->count();

        return [
            'usuarios' => User::query()->count(),
            'usuariosActivos' => User::query()->where('estado', true)->count(),
            'proveedores' => PerfilProveedor::query()->count(),
            'proveedoresAprobados' => PerfilProveedor::query()
                ->where('estado', true)
                ->where('estado_verificacion', 'aprobado')
                ->count(),
            'solicitudesPendientes' => Solicitud::query()->where('estado', 'pendiente')->count(),
            'citasProgramadas' => Cita::query()->where('estado', 'programada')->count(),
            'citasMes' => Cita::query()
                ->whereBetween('fecha_cita', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
            'citasCompletadasMes' => Cita::query()
                ->where('estado', 'completada')
                ->whereBetween('fecha_cita', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
            'totalCalificaciones' => $totalCalificaciones,
            'documentosPendientes' => DocumentoProveedor::query()->where('estado_revision', 'pendiente')->count(),
        ];
    }

    private function getResumenModulosAdmin(): array
    {
        $modulos = [
            'Usuarios' => User::query()->count(),
            'Rubros' => Rubro::query()->count(),
            'Tipos servicio' => TipoServicio::query()->count(),
            'Especialidades' => Especialidad::query()->count(),
            'Perfiles proveedor' => PerfilProveedor::query()->count(),
            'Esp. proveedor' => ProveedorEspecialidad::query()->count(),
            'Horarios' => HorarioProveedor::query()->count(),
            'Ubicaciones' => UbicacionProveedor::query()->count(),
            'Portafolio' => PortafolioProveedor::query()->count(),
            'Imagenes portafolio' => PortafolioProveedorImagen::query()->count(),
            'Tipos documento' => TipoDocumentoProveedor::query()->count(),
            'Documentos' => DocumentoProveedor::query()->count(),
            'Solicitudes' => Solicitud::query()->count(),
            'Historial solicitud' => HistorialSolicitud::query()->count(),
            'Citas' => Cita::query()->count(),
            'Calificaciones' => Calificacion::query()->count(),
            'Detalle calif.' => DetalleCalificacion::query()->count(),
            'Respuestas calif.' => RespuestaCalificacion::query()->count(),
        ];

        return [
            'labels' => array_keys($modulos),
            'series' => array_values($modulos),
        ];
    }

    private function getRendimientoCitasAdmin(): array
    {
        $resumen = $this->getResumenCitasDesdeQuery(Cita::query());
        $total = max((int) $resumen['totalAnio'], 1);

        return [
            'labels' => ['Programadas', 'En atencion', 'Completadas', 'Incidencias'],
            'series' => [
                round(((int) $resumen['programadas'] / $total) * 100),
                round(((int) $resumen['enAtencion'] / $total) * 100),
                round(((int) $resumen['completadas'] / $total) * 100),
                round(((int) $resumen['incidencias'] / $total) * 100),
            ],
            'valores' => [
                'programadas' => $resumen['programadas'],
                'enAtencion' => $resumen['enAtencion'],
                'completadas' => $resumen['completadas'],
                'incidencias' => $resumen['incidencias'],
                'total' => $resumen['totalAnio'],
                'anio' => $resumen['anio'],
            ],
        ];
    }

    private function getSolicitudesPorEstadoAdmin(): array
    {
        $estados = ['pendiente', 'aceptada', 'rechazada', 'cancelada', 'completada'];
        $registros = Solicitud::query()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->whereIn('estado', $estados)
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'labels' => ['Pendientes', 'Aceptadas', 'Rechazadas', 'Canceladas', 'Completadas'],
            'series' => collect($estados)->map(fn ($estado) => (int) ($registros[$estado] ?? 0))->values(),
        ];
    }

    private function getProveedoresPorVerificacionAdmin(): array
    {
        $estados = ['pendiente', 'aprobado', 'rechazado'];
        $registros = PerfilProveedor::query()
            ->select('estado_verificacion', DB::raw('COUNT(*) as total'))
            ->whereIn('estado_verificacion', $estados)
            ->groupBy('estado_verificacion')
            ->pluck('total', 'estado_verificacion');

        return [
            'labels' => ['Pendientes', 'Aprobados', 'Rechazados'],
            'series' => collect($estados)->map(fn ($estado) => (int) ($registros[$estado] ?? 0))->values(),
        ];
    }

    private function getEspecialidadesTopAdmin()
    {
        return Especialidad::query()
            ->with(['rubroTipoServicio.rubro', 'rubroTipoServicio.tipoServicio'])
            ->withCount([
                'proveedorEspecialidades as proveedores_count' => fn ($query) => $query->where('estado', true),
            ])
            ->orderByDesc('proveedores_count')
            ->limit(6)
            ->get()
            ->map(function (Especialidad $especialidad) {
                return [
                    'nombre' => $especialidad->nombre,
                    'rubro' => $especialidad->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro',
                    'tipoServicio' => $especialidad->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo',
                    'proveedores' => $especialidad->proveedores_count,
                ];
            });
    }

    private function getProveedoresMapaAdmin()
    {
        return PerfilProveedor::query()
            ->with(['user:id,name,avatar', 'ubicacion'])
            ->where('estado', true)
            ->where('estado_verificacion', 'aprobado')
            ->whereHas('ubicacion', function ($query) {
                $query->whereNotNull('latitud')
                    ->whereNotNull('longitud');
            })
            ->limit(40)
            ->get()
            ->map(function (PerfilProveedor $perfilProveedor) {
                $latitud = (float) $perfilProveedor->ubicacion->latitud;
                $longitud = (float) $perfilProveedor->ubicacion->longitud;

                return [
                    'nombre' => $perfilProveedor->nombre_publico,
                    'usuario' => $perfilProveedor->user?->name,
                    'zona' => $perfilProveedor->ubicacion->zona,
                    'direccion' => $perfilProveedor->ubicacion->direccion,
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                    'color' => $this->colorMapaProveedor($perfilProveedor->id),
                ];
            });
    }

    private function colorMapaProveedor(int $id): string
    {
        $colores = ['primary', 'success', 'info', 'warning', 'danger'];

        return $colores[$id % count($colores)];
    }

    private function getBackupAdmin(): array
    {
        $configuracion = ConfiguracionBackup::query()->first();
        $archivos = collect(Storage::disk('backups')->allFiles())
            ->filter(fn ($archivo) => str_ends_with($archivo, '.zip'))
            ->sortByDesc(fn ($archivo) => Storage::disk('backups')->lastModified($archivo))
            ->values();

        $ultimoArchivo = $archivos->first();

        return [
            'activo' => (bool) ($configuracion?->activo ?? false),
            'frecuencia' => $configuracion?->frecuencia ?? 'Sin configurar',
            'hora' => $configuracion?->hora_ejecucion ? substr($configuracion->hora_ejecucion, 0, 5) : '--:--',
            'ultimoEstado' => $configuracion?->ultimo_estado ?? 'sin_registro',
            'ultimoBackup' => $configuracion?->ultimo_backup_at,
            'ultimoMensaje' => $configuracion?->ultimo_mensaje,
            'totalArchivos' => $archivos->count(),
            'ultimoArchivo' => $ultimoArchivo ? basename($ultimoArchivo) : null,
            'ultimoPeso' => $ultimoArchivo ? $this->formatearBytes(Storage::disk('backups')->size($ultimoArchivo)) : '0 B',
        ];
    }

    private function getEvolutionApiAdmin(): array
    {
        $config = config('services.evolution_api');

        if (! ($config['enabled'] ?? false)) {
            return [
                'estado' => 'inactivo',
                'mensaje' => 'Evolution API esta desactivado en el sistema.',
                'detalle' => 'EVOLUTION_API_ENABLED=false',
                'clase' => 'secondary',
            ];
        }

        if (blank($config['url'] ?? null) || blank($config['key'] ?? null) || blank($config['instance'] ?? null)) {
            return [
                'estado' => 'incompleto',
                'mensaje' => 'Faltan variables de entorno para Evolution API.',
                'detalle' => 'Revisa URL, API KEY e instancia.',
                'clase' => 'warning',
            ];
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'apikey' => $config['key'],
                ])
                ->get(rtrim($config['url'], '/') . '/instance/connectionState/' . $config['instance']);

            if (! $response->successful()) {
                return [
                    'estado' => 'error',
                    'mensaje' => 'Evolution API responde, pero no confirma la instancia.',
                    'detalle' => 'HTTP ' . $response->status(),
                    'clase' => 'danger',
                ];
            }

            $estado = strtolower((string) data_get($response->json(), 'instance.state', data_get($response->json(), 'state', 'desconocido')));
            $conectado = in_array($estado, ['open', 'connected'], true);

            return [
                'estado' => $conectado ? 'conectado' : $estado,
                'mensaje' => $conectado ? 'WhatsApp esta conectado y listo para enviar.' : 'La instancia existe, pero no esta conectada.',
                'detalle' => 'Instancia: ' . $config['instance'],
                'clase' => $conectado ? 'success' : 'warning',
            ];
        } catch (\Throwable $e) {
            return [
                'estado' => 'sin conexion',
                'mensaje' => 'No se pudo contactar con Evolution API.',
                'detalle' => $e->getMessage(),
                'clase' => 'danger',
            ];
        }
    }

    private function getBusquedaInteligenteAdmin(): array
    {
        $config = config('services.busqueda_inteligente');
        $url = (string) ($config['url'] ?? '');

        if (blank($url)) {
            return [
                'estado' => 'incompleto',
                'mensaje' => 'No se configuro la URL de la busqueda inteligente.',
                'detalle' => 'Revisa BUSQUEDA_INTELIGENTE_URL.',
                'clase' => 'warning',
            ];
        }

        try {
            $response = Http::timeout((int) ($config['timeout'] ?? 30))
                ->acceptJson()
                ->get($url);

            if (in_array($response->status(), [200, 405, 422], true)) {
                return [
                    'estado' => 'activo',
                    'mensaje' => 'La API de busqueda inteligente esta disponible.',
                    'detalle' => 'Endpoint: ' . $url,
                    'clase' => 'success',
                ];
            }

            return [
                'estado' => 'error',
                'mensaje' => 'La API de busqueda inteligente respondio con error.',
                'detalle' => 'HTTP ' . $response->status(),
                'clase' => 'danger',
            ];
        } catch (\Throwable $e) {
            return [
                'estado' => 'sin conexion',
                'mensaje' => 'No se pudo contactar con la busqueda inteligente.',
                'detalle' => $e->getMessage(),
                'clase' => 'danger',
            ];
        }
    }

    private function formatearBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }

    private function getActividadRecienteAdmin()
    {
        return Activity::query()
            ->with('causer')
            ->latest()
            ->take(8)
            ->get();
    }

    private function getResumenCitas(PerfilProveedor $perfilProveedor): array
    {
        $query = Cita::query()
            ->whereHas('solicitud', function ($query) use ($perfilProveedor) {
                $query->where('perfil_proveedor_id', $perfilProveedor->id);
            });

        return $this->getResumenCitasDesdeQuery($query);
    }

    private function getClienteData(User $cliente): array
    {
        return [
            'usuario' => $cliente,
            'resumenCitas' => $this->getResumenCitasCliente($cliente),
            'proveedoresAtendidos' => $this->getProveedoresAtendidosCliente($cliente),
        ];
    }

    private function getResumenCitasCliente(User $cliente): array
    {
        $query = Cita::query()
            ->whereHas('solicitud', function ($query) use ($cliente) {
                $query->where('cliente_user_id', $cliente->id);
            });

        return $this->getResumenCitasDesdeQuery($query);
    }

    private function getProveedoresAtendidosCliente(User $cliente)
    {
        return PerfilProveedor::query()
            ->with('user:id,name,avatar')
            ->whereHas('solicitudes', function ($query) use ($cliente) {
                $query->where('cliente_user_id', $cliente->id)
                    ->whereHas('cita', fn ($subQuery) => $subQuery->where('estado', 'completada'));
            })
            ->withCount([
                'solicitudes as citas_cliente_completadas_count' => function ($query) use ($cliente) {
                    $query->where('cliente_user_id', $cliente->id)
                        ->whereHas('cita', fn ($subQuery) => $subQuery->where('estado', 'completada'));
                },
            ])
            ->orderByDesc('citas_cliente_completadas_count')
            ->limit(12)
            ->get()
            ->map(function (PerfilProveedor $perfilProveedor) {
                $calificacionesQuery = Calificacion::query()
                    ->where('estado', 'visible')
                    ->whereHas('cita.solicitud', function ($query) use ($perfilProveedor) {
                        $query->where('perfil_proveedor_id', $perfilProveedor->id);
                    });

                $totalCalificaciones = (clone $calificacionesQuery)->count();
                $promedio = $totalCalificaciones > 0
                    ? round((clone $calificacionesQuery)->avg('puntuacion'), 1)
                    : 0;

                $mensuales = Calificacion::query()
                    ->join('citas', 'citas.id', '=', 'calificaciones.cita_id')
                    ->join('solicitudes', 'solicitudes.id', '=', 'citas.solicitud_id')
                    ->where('calificaciones.estado', 'visible')
                    ->where('solicitudes.perfil_proveedor_id', $perfilProveedor->id)
                    ->whereBetween('citas.fecha_cita', [
                        now()->startOfYear()->toDateString(),
                        now()->endOfYear()->toDateString(),
                    ])
                    ->selectRaw('MONTH(citas.fecha_cita) as mes')
                    ->selectRaw('AVG(puntuacion) as promedio')
                    ->groupBy('mes')
                    ->get()
                    ->keyBy('mes');

                $serie = [];

                for ($mes = 1; $mes <= 12; $mes++) {
                    $serie[] = round((float) ($mensuales->get($mes)->promedio ?? 0), 1);
                }

                return [
                    'nombre' => $perfilProveedor->nombre_publico,
                    'usuario' => $perfilProveedor->user?->name ?? 'Proveedor',
                    'avatar' => $perfilProveedor->user?->avatar_url,
                    'inicial' => strtoupper(substr($perfilProveedor->nombre_publico ?: 'P', 0, 1)),
                    'promedio' => $promedio,
                    'totalCalificaciones' => $totalCalificaciones,
                    'citasCliente' => $perfilProveedor->citas_cliente_completadas_count,
                    'serie' => $serie,
                ];
            });
    }

    private function getResumenCitasDesdeQuery($query): array
    {
        $inicioAnio = now()->startOfYear()->toDateString();
        $finAnio = now()->endOfYear()->toDateString();

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
