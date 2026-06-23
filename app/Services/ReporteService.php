<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Cita;
use App\Models\ConfiguracionBackup;
use App\Models\ConfiguracionReporte;
use App\Models\DocumentoProveedor;
use App\Models\PerfilProveedor;
use App\Models\Reporte;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;

class ReporteService
{
    public function tipos(): array
    {
        return config('reportes.tipos', []);
    }

    public function reportes()
    {
        return Reporte::query()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);
    }

    public function resumenIndex(): array
    {
        return [
            'total' => Reporte::count(),
            'activos' => Reporte::where('estado', true)->count(),
            'conGraficas' => Reporte::where('incluir_graficas', true)->count(),
            'conImagenes' => Reporte::where('incluir_imagenes', true)->count(),
        ];
    }

    public function crear(array $data): Reporte
    {
        return Reporte::create($this->normalizarData($data) + [
            'user_id' => auth()->id(),
        ]);
    }

    public function actualizar(Reporte $reporte, array $data): Reporte
    {
        $reporte->update($this->normalizarData($data, $reporte));

        return $reporte;
    }

    public function eliminar(Reporte $reporte): Reporte
    {
        $reporte->update([
            'estado' => false,
        ]);

        return $reporte;
    }

    public function datosPdf(Reporte $reporte): array
    {
        return [
            'reporte' => $reporte->load('user:id,name,email'),
            'tipoNombre' => $this->tipos()[$reporte->tipo] ?? $reporte->tipo,
            'configuracionPdf' => $this->configuracionPdf(),
            'generadoPor' => auth()->user(),
            'generadoEn' => now(),
            'datos' => match ($reporte->tipo) {
                'proveedores' => $this->datosProveedores($reporte),
                'solicitudes_citas' => $this->datosSolicitudesCitas($reporte),
                'calificaciones' => $this->datosCalificaciones($reporte),
                'documentos' => $this->datosDocumentos($reporte),
                'backups' => $this->datosBackups(),
                'activity_logs' => $this->datosActivityLogs($reporte),
                default => $this->datosResumenGeneral($reporte),
            },
        ];
    }

    public function generarPdf(Reporte $reporte)
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            throw new RuntimeException('Falta instalar barryvdh/laravel-dompdf. Ejecuta: composer require barryvdh/laravel-dompdf');
        }

        $datos = $this->datosPdf($reporte);
        $configuracion = $datos['configuracionPdf'];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reportes.pdf.reporte', $datos)
            ->setPaper($configuracion->tamano_hoja ?: 'letter', $configuracion->orientacion ?: 'portrait')
            ->download($this->nombreArchivo($reporte));
    }

    public function configuracionPdf(): ConfiguracionReporte
    {
        return ConfiguracionReporte::firstOrCreate(
            ['id' => 1],
            [
                'tamano_hoja' => 'letter',
                'orientacion' => 'portrait',
                'color_principal' => '#635bff',
                'texto_pie' => 'Proyecto Integrador - Reporte generado automaticamente desde el sistema.',
                'mostrar_logo' => true,
                'mostrar_fecha' => true,
                'mostrar_generado_por' => true,
            ]
        );
    }

    public function actualizarConfiguracionPdf(array $data): ConfiguracionReporte
    {
        $configuracion = $this->configuracionPdf();

        $configuracion->update([
            'tamano_hoja' => $data['tamano_hoja'] ?? 'letter',
            'orientacion' => $data['orientacion'] ?? 'portrait',
            'logo_path' => $this->resolverLogoConfiguracion($data, $configuracion),
            'color_principal' => $data['color_principal'] ?? '#635bff',
            'titulo_encabezado' => $data['titulo_encabezado'] ?? null,
            'texto_pie' => $data['texto_pie'] ?? 'Proyecto Integrador - Reporte generado automaticamente desde el sistema.',
            'mostrar_logo' => (bool) ($data['mostrar_logo'] ?? false),
            'mostrar_fecha' => (bool) ($data['mostrar_fecha'] ?? false),
            'mostrar_generado_por' => (bool) ($data['mostrar_generado_por'] ?? false),
        ]);

        return $configuracion;
    }

    private function normalizarData(array $data, ?Reporte $reporte = null): array
    {
        return [
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'incluir_graficas' => (bool) ($data['incluir_graficas'] ?? false),
            'incluir_imagenes' => (bool) ($data['incluir_imagenes'] ?? false),
            'estado' => (bool) ($data['estado'] ?? true),
            'opciones' => $this->normalizarOpciones($data),
        ];
    }

    private function resolverLogoConfiguracion(array $data, ConfiguracionReporte $configuracion): ?string
    {
        if (! empty($data['quitar_logo'])) {
            $this->eliminarArchivoPublico($configuracion->logo_path);

            return null;
        }

        if (! empty($data['logo'])) {
            $this->eliminarArchivoPublico($configuracion->logo_path);

            $archivo = $data['logo'];
            $nombre = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
            $directorio = public_path('uploads/reportes/logos');

            if (! File::exists($directorio)) {
                File::makeDirectory($directorio, 0755, true);
            }

            $archivo->move($directorio, $nombre);

            return 'uploads/reportes/logos/' . $nombre;
        }

        return $configuracion->logo_path;
    }

    private function eliminarArchivoPublico(?string $ruta): void
    {
        if (! $ruta) {
            return;
        }

        $rutaAbsoluta = public_path(ltrim($ruta, '/'));

        if (File::exists($rutaAbsoluta)) {
            File::delete($rutaAbsoluta);
        }
    }

    private function normalizarOpciones(array $data): array
    {
        $tipo = $data['tipo'] ?? null;
        $opciones = $data['opciones'] ?? [];
        $configuracion = config("reportes.opciones.{$tipo}", []);
        $resultado = [];

        foreach (($configuracion['selects'] ?? []) as $clave => $select) {
            $resultado[$clave] = $opciones[$clave] ?? array_key_first($select['opciones']);
        }

        foreach (($configuracion['switches'] ?? []) as $clave => $label) {
            $resultado[$clave] = (bool) ($opciones[$clave] ?? false);
        }

        return $resultado;
    }

    private function opcion(Reporte $reporte, string $clave, mixed $default = null): mixed
    {
        return data_get($reporte->opciones ?? [], $clave, $default);
    }

    private function aplicarRango(Builder $query, Reporte $reporte, string $campo = 'created_at'): Builder
    {
        return $query
            ->when($reporte->fecha_inicio, fn ($query) => $query->whereDate($campo, '>=', $reporte->fecha_inicio))
            ->when($reporte->fecha_fin, fn ($query) => $query->whereDate($campo, '<=', $reporte->fecha_fin));
    }

    private function datosResumenGeneral(Reporte $reporte): array
    {
        $tarjetas = collect([
            'incluir_usuarios' => ['titulo' => 'Usuarios', 'valor' => User::count(), 'detalle' => User::where('estado', true)->count() . ' activos'],
            'incluir_proveedores' => ['titulo' => 'Proveedores', 'valor' => PerfilProveedor::count(), 'detalle' => PerfilProveedor::where('estado_verificacion', 'aprobado')->count() . ' aprobados'],
            'incluir_solicitudes' => ['titulo' => 'Solicitudes', 'valor' => Solicitud::count(), 'detalle' => Solicitud::where('estado', 'pendiente')->count() . ' pendientes'],
            'incluir_citas' => ['titulo' => 'Citas', 'valor' => Cita::count(), 'detalle' => Cita::where('estado', 'completada')->count() . ' completadas'],
            'incluir_calificaciones' => ['titulo' => 'Calificaciones', 'valor' => Calificacion::where('estado', 'visible')->count(), 'detalle' => round((float) Calificacion::where('estado', 'visible')->avg('puntuacion'), 1) . ' promedio'],
            'incluir_documentos' => ['titulo' => 'Documentos', 'valor' => DocumentoProveedor::count(), 'detalle' => DocumentoProveedor::where('estado_revision', 'pendiente')->count() . ' por revisar'],
        ])
            ->filter(fn ($tarjeta, $clave) => (bool) $this->opcion($reporte, $clave, true))
            ->values()
            ->all();

        return [
            'tarjetas' => $tarjetas,
            'graficas' => [
                'solicitudes' => $this->conteoPorEstado(Solicitud::query(), ['pendiente', 'aceptada', 'rechazada', 'cancelada', 'finalizada']),
                'citas' => $this->conteoPorEstado(Cita::query(), ['programada', 'en_atencion', 'completada', 'cancelada', 'no_asistio', 'vencida']),
            ],
            'filas' => $this->aplicarRango(Solicitud::query()->with(['cliente:id,name', 'perfilProveedor:id,nombre_publico']), $reporte)
                ->latest()
                ->get(),
        ];
    }

    private function datosProveedores(Reporte $reporte): array
    {
        $estadoVerificacion = $this->opcion($reporte, 'estado_verificacion', 'todos');

        $proveedores = $this->aplicarRango(
            PerfilProveedor::query()->with(['user:id,name,email,celular,avatar', 'ubicacion', 'proveedorEspecialidades.especialidad']),
            $reporte
        )
            ->when($estadoVerificacion !== 'todos', fn ($query) => $query->where('estado_verificacion', $estadoVerificacion))
            ->when($this->opcion($reporte, 'solo_con_ubicacion', false), fn ($query) => $query->whereHas('ubicacion'))
            ->when($this->opcion($reporte, 'solo_con_documentos', false), fn ($query) => $query->whereHas('documentos'))
            ->latest()
            ->get();

        return [
            'tarjetas' => [
                ['titulo' => 'Total proveedores', 'valor' => PerfilProveedor::count(), 'detalle' => 'Perfiles registrados'],
                ['titulo' => 'Aprobados', 'valor' => PerfilProveedor::where('estado_verificacion', 'aprobado')->count(), 'detalle' => 'Listos para atencion'],
                ['titulo' => 'Pendientes', 'valor' => PerfilProveedor::where('estado_verificacion', 'pendiente')->count(), 'detalle' => 'En revision'],
                ['titulo' => 'Rechazados', 'valor' => PerfilProveedor::where('estado_verificacion', 'rechazado')->count(), 'detalle' => 'Observados'],
            ],
            'graficas' => [
                'verificacion' => $this->conteoPorCampo(PerfilProveedor::query(), 'estado_verificacion', ['pendiente', 'aprobado', 'rechazado']),
            ],
            'filas' => $proveedores,
            'imagenes' => $reporte->incluir_imagenes
                ? $proveedores->take(6)->map(fn ($proveedor) => $this->imagenProveedor($proveedor))->filter()->values()
                : collect(),
        ];
    }

    private function datosSolicitudesCitas(Reporte $reporte): array
    {
        $estadoSolicitud = $this->opcion($reporte, 'estado_solicitud', 'todos');
        $estadoCita = $this->opcion($reporte, 'estado_cita', 'todos');

        return [
            'tarjetas' => [
                ['titulo' => 'Solicitudes', 'valor' => Solicitud::count(), 'detalle' => 'Total registradas'],
                ['titulo' => 'Pendientes', 'valor' => Solicitud::where('estado', 'pendiente')->count(), 'detalle' => 'Esperan respuesta'],
                ['titulo' => 'Citas', 'valor' => Cita::count(), 'detalle' => 'Total agendadas'],
                ['titulo' => 'Completadas', 'valor' => Cita::where('estado', 'completada')->count(), 'detalle' => 'Atenciones finalizadas'],
            ],
            'graficas' => [
                'solicitudes' => $this->conteoPorEstado(Solicitud::query(), ['pendiente', 'aceptada', 'rechazada', 'cancelada', 'finalizada']),
                'citas' => $this->conteoPorEstado(Cita::query(), ['programada', 'en_atencion', 'completada', 'cancelada', 'no_asistio', 'vencida']),
            ],
            'filas' => $this->aplicarRango(Cita::query()->with([
                'solicitud.cliente:id,name',
                'solicitud.perfilProveedor:id,nombre_publico',
                'solicitud.especialidad:id,nombre',
            ]), $reporte, 'fecha_cita')
                ->when($estadoCita !== 'todos', fn ($query) => $query->where('estado', $estadoCita))
                ->when($estadoSolicitud !== 'todos', fn ($query) => $query->whereHas('solicitud', fn ($subQuery) => $subQuery->where('estado', $estadoSolicitud)))
                ->latest('fecha_cita')
                ->get(),
        ];
    }

    private function datosCalificaciones(Reporte $reporte): array
    {
        $estadoCalificacion = $this->opcion($reporte, 'estado_calificacion', 'todos');
        $puntuacionMinima = $this->opcion($reporte, 'puntuacion_minima', 'todas');

        $calificaciones = $this->aplicarRango(Calificacion::query()->with([
            'cita.solicitud.cliente:id,name',
            'cita.solicitud.perfilProveedor:id,nombre_publico',
            'detalles.aspectoCalificacion',
            'respuesta',
        ]), $reporte)
            ->when($estadoCalificacion !== 'todos', fn ($query) => $query->where('estado', $estadoCalificacion))
            ->when($puntuacionMinima !== 'todas', fn ($query) => $query->where('puntuacion', '>=', (int) $puntuacionMinima))
            ->latest()
            ->get();

        return [
            'tarjetas' => [
                ['titulo' => 'Resenas visibles', 'valor' => Calificacion::where('estado', 'visible')->count(), 'detalle' => 'Publicadas'],
                ['titulo' => 'Promedio', 'valor' => round((float) Calificacion::where('estado', 'visible')->avg('puntuacion'), 1), 'detalle' => 'Puntuacion general'],
                ['titulo' => 'Respondidas', 'valor' => Calificacion::whereHas('respuesta')->count(), 'detalle' => 'Con respuesta proveedor'],
                ['titulo' => 'Ocultas', 'valor' => Calificacion::where('estado', 'oculta')->count(), 'detalle' => 'Moderadas'],
            ],
            'graficas' => [
                'estrellas' => $this->conteoPorCampo(Calificacion::query(), 'puntuacion', [5, 4, 3, 2, 1]),
            ],
            'filas' => $calificaciones,
        ];
    }

    private function datosDocumentos(Reporte $reporte): array
    {
        $estadoRevision = $this->opcion($reporte, 'estado_revision', 'todos');

        $documentos = $this->aplicarRango(DocumentoProveedor::query()->with([
            'perfilProveedor:id,nombre_publico',
            'tipoDocumentoProveedor:id,nombre',
        ]), $reporte)
            ->when($estadoRevision !== 'todos', fn ($query) => $query->where('estado_revision', $estadoRevision))
            ->when($this->opcion($reporte, 'solo_con_archivo', false), fn ($query) => $query->whereNotNull('archivo'))
            ->latest()
            ->get();

        return [
            'tarjetas' => [
                ['titulo' => 'Documentos', 'valor' => DocumentoProveedor::count(), 'detalle' => 'Total subidos'],
                ['titulo' => 'Aprobados', 'valor' => DocumentoProveedor::where('estado_revision', 'aprobado')->count(), 'detalle' => 'Validados'],
                ['titulo' => 'Pendientes', 'valor' => DocumentoProveedor::where('estado_revision', 'pendiente')->count(), 'detalle' => 'Por revisar'],
                ['titulo' => 'Rechazados', 'valor' => DocumentoProveedor::where('estado_revision', 'rechazado')->count(), 'detalle' => 'Observados'],
            ],
            'graficas' => [
                'revision' => $this->conteoPorCampo(DocumentoProveedor::query(), 'estado_revision', ['pendiente', 'aprobado', 'rechazado']),
            ],
            'filas' => $documentos,
            'imagenes' => $reporte->incluir_imagenes
                ? $documentos->take(6)->map(fn ($documento) => $this->imagenArchivo($documento->archivo))->filter()->values()
                : collect(),
        ];
    }

    private function datosBackups(): array
    {
        $configuracion = ConfiguracionBackup::first();
        $archivos = collect(Storage::disk('backups')->allFiles())
            ->filter(fn ($archivo) => str_ends_with($archivo, '.zip'))
            ->sortByDesc(fn ($archivo) => Storage::disk('backups')->lastModified($archivo))
            ->take(15)
            ->map(fn ($archivo) => [
                'nombre' => basename($archivo),
                'tamano' => Storage::disk('backups')->size($archivo),
                'fecha' => Storage::disk('backups')->lastModified($archivo),
            ])
            ->values();

        return [
            'tarjetas' => [
                ['titulo' => 'Backup automatico', 'valor' => $configuracion?->activo ? 'Activo' : 'Inactivo', 'detalle' => $configuracion?->frecuencia ?? 'Sin configurar'],
                ['titulo' => 'Hora', 'valor' => $configuracion?->hora_ejecucion ? substr($configuracion->hora_ejecucion, 0, 5) : '--:--', 'detalle' => 'Programada'],
                ['titulo' => 'Archivos', 'valor' => $archivos->count(), 'detalle' => 'Backups locales'],
                ['titulo' => 'Ultimo estado', 'valor' => $configuracion?->ultimo_estado ?? 'Sin registro', 'detalle' => $configuracion?->ultimo_backup_at?->format('d/m/Y H:i') ?? 'Sin fecha'],
            ],
            'filas' => $archivos,
        ];
    }

    private function datosActivityLogs(Reporte $reporte): array
    {
        $actor = $this->opcion($reporte, 'actor', 'todos');

        $logs = $this->aplicarRango(Activity::query()->with('causer:id,name'), $reporte)
            ->when($actor === 'usuarios', fn ($query) => $query->whereNotNull('causer_id'))
            ->when($actor === 'sistema', fn ($query) => $query->whereNull('causer_id'))
            ->latest()
            ->get();

        return [
            'tarjetas' => [
                ['titulo' => 'Movimientos', 'valor' => Activity::count(), 'detalle' => 'Total auditado'],
                ['titulo' => 'Usuarios con actividad', 'valor' => Activity::whereNotNull('causer_id')->distinct('causer_id')->count('causer_id'), 'detalle' => 'Actores registrados'],
                ['titulo' => 'Modulo mas activo', 'valor' => Activity::query()->selectRaw('log_name, COUNT(*) total')->groupBy('log_name')->orderByDesc('total')->value('log_name') ?? 'Sin datos', 'detalle' => 'Segun activity log'],
                ['titulo' => 'Hoy', 'valor' => Activity::whereDate('created_at', today())->count(), 'detalle' => 'Movimientos del dia'],
            ],
            'graficas' => [
                'modulos' => Activity::query()
                    ->selectRaw('log_name as estado, COUNT(*) as total')
                    ->groupBy('log_name')
                    ->orderByDesc('total')
                    ->limit(8)
                    ->pluck('total', 'estado')
                    ->toArray(),
            ],
            'filas' => $logs,
        ];
    }

    private function conteoPorEstado(Builder $query, array $estados): array
    {
        return $this->conteoPorCampo($query, 'estado', $estados);
    }

    private function conteoPorCampo(Builder $query, string $campo, array $valores): array
    {
        $registros = $query
            ->select($campo, DB::raw('COUNT(*) as total'))
            ->whereIn($campo, $valores)
            ->groupBy($campo)
            ->pluck('total', $campo);

        return collect($valores)
            ->mapWithKeys(fn ($valor) => [(string) $valor => (int) ($registros[$valor] ?? 0)])
            ->all();
    }

    private function imagenProveedor(PerfilProveedor $proveedor): ?array
    {
        if ($proveedor->foto_portada) {
            return $this->imagenArchivo($proveedor->foto_portada, $proveedor->nombre_publico);
        }

        if ($proveedor->user?->avatar) {
            return $this->imagenArchivo($proveedor->user->avatar, $proveedor->nombre_publico);
        }

        return null;
    }

    private function imagenArchivo(?string $ruta, ?string $titulo = null): ?array
    {
        if (! $ruta || str_ends_with(strtolower($ruta), '.pdf')) {
            return null;
        }

        $ruta = ltrim($ruta, '/');
        $rutaAbsoluta = public_path($ruta);

        if (! file_exists($rutaAbsoluta)) {
            return null;
        }

        return [
            'titulo' => $titulo ?? basename($ruta),
            'ruta' => $rutaAbsoluta,
        ];
    }

    private function nombreArchivo(Reporte $reporte): string
    {
        return Str::slug('reporte-' . $reporte->tipo . '-' . now()->format('Ymd-His')) . '.pdf';
    }
}
