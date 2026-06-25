@php
    $resumen = $data['resumen'];
    $backup = $data['backup'];
    $evolutionApi = $data['evolutionApi'];

    $modulosChart = [
        'labels' => $data['modulos']['labels'],
        'series' => $data['modulos']['series'],
    ];

    $rendimientoCitasChart = [
        'labels' => $data['rendimientoCitas']['labels'],
        'series' => $data['rendimientoCitas']['series'],
    ];

    $solicitudesChart = [
        'labels' => $data['solicitudesPorEstado']['labels'],
        'series' => $data['solicitudesPorEstado']['series']->toArray(),
    ];

    $proveedoresChart = [
        'labels' => $data['proveedoresPorVerificacion']['labels'],
        'series' => $data['proveedoresPorVerificacion']['series']->toArray(),
    ];

    $backupEstadoClass = match ($backup['ultimoEstado']) {
        'correcto' => 'success',
        'error' => 'danger',
        default => 'warning',
    };
@endphp

<div class="inicio-admin">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Inicio Administrador</h4>
                    <p class="text-muted mb-0">Vista general del estado operativo del sistema.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card inicio-admin-kpi h-100">
                <div class="card-body">
                    <span class="inicio-stat-icon bg-primary-subtle text-primary">
                        <i class="ri-user-3-line"></i>
                    </span>
                    <p class="text-muted fw-medium mb-2">Usuarios</p>
                    <h3 class="mb-1">{{ $resumen['usuarios'] }}</h3>
                    <small class="text-muted">{{ $resumen['usuariosActivos'] }} activos</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card inicio-admin-kpi h-100">
                <div class="card-body">
                    <span class="inicio-stat-icon bg-success-subtle text-success">
                        <i class="ri-shield-user-line"></i>
                    </span>
                    <p class="text-muted fw-medium mb-2">Proveedores aprobados</p>
                    <h3 class="mb-1">{{ $resumen['proveedoresAprobados'] }}</h3>
                    <small class="text-muted">De {{ $resumen['proveedores'] }} perfiles</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card inicio-admin-kpi h-100">
                <div class="card-body">
                    <span class="inicio-stat-icon bg-warning-subtle text-warning">
                        <i class="ri-calendar-event-line"></i>
                    </span>
                    <p class="text-muted fw-medium mb-2">Citas del mes</p>
                    <h3 class="mb-1">{{ $resumen['citasMes'] }}</h3>
                    <small class="text-muted">{{ $resumen['citasCompletadasMes'] }} completadas</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card inicio-admin-kpi h-100">
                <div class="card-body">
                    <span class="inicio-stat-icon bg-info-subtle text-info">
                        <i class="ri-file-shield-2-line"></i>
                    </span>
                    <p class="text-muted fw-medium mb-2">Documentos por revisar</p>
                    <h3 class="mb-1">{{ $resumen['documentosPendientes'] }}</h3>
                    <small class="text-muted">Pendientes de verificacion</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card inicio-admin-rendimiento-card mb-3">
        <div class="card-header align-items-center d-flex flex-wrap gap-2">
            <div class="flex-grow-1">
                <h5 class="card-title mb-1">Rendimiento general de citas</h5>
                <p class="text-muted mb-0">Proporcion de estados durante {{ $data['rendimientoCitas']['valores']['anio'] }}.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary">
                {{ $data['rendimientoCitas']['valores']['total'] }} citas
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-xl-5">
                    <div
                        class="inicio-admin-radial"
                        data-inicio-admin-citas-radial
                        data-chart='@json($rendimientoCitasChart)'
                    ></div>
                </div>
                <div class="col-xl-7">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="inicio-admin-rendimiento-item">
                                <span class="bg-info-subtle text-info"><i class="ri-time-line"></i></span>
                                <div>
                                    <h4>{{ $data['rendimientoCitas']['valores']['programadas'] }}</h4>
                                    <p>Programadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="inicio-admin-rendimiento-item">
                                <span class="bg-primary-subtle text-primary"><i class="ri-customer-service-2-line"></i></span>
                                <div>
                                    <h4>{{ $data['rendimientoCitas']['valores']['enAtencion'] }}</h4>
                                    <p>En atencion</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="inicio-admin-rendimiento-item">
                                <span class="bg-success-subtle text-success"><i class="ri-checkbox-circle-line"></i></span>
                                <div>
                                    <h4>{{ $data['rendimientoCitas']['valores']['completadas'] }}</h4>
                                    <p>Completadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="inicio-admin-rendimiento-item">
                                <span class="bg-warning-subtle text-warning"><i class="ri-alert-line"></i></span>
                                <div>
                                    <h4>{{ $data['rendimientoCitas']['valores']['incidencias'] }}</h4>
                                    <p>Canceladas o vencidas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Movimiento por modulo</h5>
                        <p class="text-muted mb-0">Volumen acumulado de modelos funcionales del sistema.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div
                        class="inicio-admin-chart"
                        data-inicio-admin-modulos-chart
                        data-chart='@json($modulosChart)'
                    ></div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Estado de solicitudes</h5>
                        <p class="text-muted mb-0">Distribucion actual del flujo de atencion.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div
                        class="inicio-admin-donut"
                        data-inicio-admin-solicitudes-chart
                        data-chart='@json($solicitudesChart)'
                    ></div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="inicio-admin-backup-icon bg-{{ $evolutionApi['clase'] }}-subtle text-{{ $evolutionApi['clase'] }}">
                            <i class="ri-whatsapp-line"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <h5 class="mb-0">Evolution API</h5>
                                <span class="badge bg-{{ $evolutionApi['clase'] }}-subtle text-{{ $evolutionApi['clase'] }} text-capitalize">
                                    {{ $evolutionApi['estado'] }}
                                </span>
                            </div>
                            <p class="text-muted mb-1">{{ $evolutionApi['mensaje'] }}</p>
                            <small class="text-muted">{{ $evolutionApi['detalle'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="inicio-admin-backup-icon bg-{{ $data['busquedaInteligente']['clase'] }}-subtle text-{{ $data['busquedaInteligente']['clase'] }}">
                            <i class="ri-brain-line"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <h5 class="mb-0">Busqueda Inteligente</h5>
                                <span class="badge bg-{{ $data['busquedaInteligente']['clase'] }}-subtle text-{{ $data['busquedaInteligente']['clase'] }} text-capitalize">
                                    {{ $data['busquedaInteligente']['estado'] }}
                                </span>
                            </div>
                            <p class="text-muted mb-1">{{ $data['busquedaInteligente']['mensaje'] }}</p>
                            <small class="text-muted">{{ $data['busquedaInteligente']['detalle'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-xl-7">
            <div class="card h-100 inicio-admin-map-card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Proveedores aprobados en La Paz</h5>
                        <p class="text-muted mb-0">Puntos registrados por proveedores aprobados con ubicacion activa.</p>
                    </div>
                    <span class="badge bg-success-subtle text-success">
                        {{ $data['proveedoresMapa']->count() }} ubicados
                    </span>
                </div>
                <div class="card-body">
                    <div class="mapa-lapaz-card inicio-admin-vector-map-card">
                        <div class="mapa-lapaz-header">
                            <h5>Mapa del Municipio de La Paz</h5>
                            <p>Zonas del GeoJSON y puntos de proveedores aprobados.</p>
                        </div>
                        <div class="inicio-admin-vector-map-wrap" data-inicio-admin-mapa-wrap>
                        <div
                            id="mapaLaPazAdminProveedores"
                            class="mapa-lapaz-canvas inicio-admin-vector-map"
                            data-mapa-lapaz
                            data-mapa-estatico="true"
                        ></div>

                        @forelse ($data['proveedoresMapa'] as $proveedor)
                            <button
                                type="button"
                                class="inicio-admin-map-marker bg-{{ $proveedor['color'] }}"
                                data-inicio-admin-map-marker
                                data-latitud="{{ $proveedor['latitud'] }}"
                                data-longitud="{{ $proveedor['longitud'] }}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="{{ $proveedor['nombre'] }} - {{ $proveedor['zona'] }}"
                            >
                                <i class="ri-map-pin-fill"></i>
                            </button>
                        @empty
                            <div class="inicio-admin-map-empty">
                                <i class="ri-map-pin-line"></i>
                                <span>No hay proveedores aprobados con ubicacion.</span>
                            </div>
                        @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Proveedores por verificacion</h5>
                        <p class="text-muted mb-0">Estado administrativo de perfiles.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div
                        class="inicio-admin-donut"
                        data-inicio-admin-proveedores-chart
                        data-chart='@json($proveedoresChart)'
                    ></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1">Backups</h5>
                    <p class="text-muted mb-0">Ultimo estado registrado del respaldo.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="inicio-admin-backup-icon bg-{{ $backupEstadoClass }}-subtle text-{{ $backupEstadoClass }}">
                            <i class="ri-database-2-line"></i>
                        </span>
                        <div>
                            <span class="badge bg-{{ $backupEstadoClass }}-subtle text-{{ $backupEstadoClass }} text-capitalize">
                                {{ str_replace('_', ' ', $backup['ultimoEstado']) }}
                            </span>
                            <h5 class="mb-0 mt-2">{{ $backup['ultimoArchivo'] ?? 'Sin backup generado' }}</h5>
                        </div>
                    </div>

                    <div class="inicio-admin-backup-row">
                        <span>Automatico</span>
                        <strong>{{ $backup['activo'] ? 'Activo' : 'Inactivo' }}</strong>
                    </div>
                    <div class="inicio-admin-backup-row">
                        <span>Frecuencia</span>
                        <strong class="text-capitalize">{{ $backup['frecuencia'] }}</strong>
                    </div>
                    <div class="inicio-admin-backup-row">
                        <span>Hora</span>
                        <strong>{{ $backup['hora'] }}</strong>
                    </div>
                    <div class="inicio-admin-backup-row">
                        <span>Archivos</span>
                        <strong>{{ $backup['totalArchivos'] }} backups</strong>
                    </div>
                    <div class="inicio-admin-backup-row">
                        <span>Peso ultimo</span>
                        <strong>{{ $backup['ultimoPeso'] }}</strong>
                    </div>

                    @can('ver backups')
                        <a href="{{ route('backups.index') }}" class="btn btn-soft-primary w-100 mt-3">
                            <i class="ri-eye-line align-bottom me-1"></i>
                            Ver configuracion
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1">Especialidades con mas proveedores</h5>
                    <p class="text-muted mb-0">Oferta activa segun especialidad.</p>
                </div>
                <div class="card-body inicio-admin-list">
                    @forelse ($data['especialidadesTop'] as $especialidad)
                        <div class="inicio-admin-list-item">
                            <span class="inicio-admin-list-icon bg-primary-subtle text-primary">
                                <i class="ri-tools-line"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $especialidad['nombre'] }}</h6>
                                <small class="text-muted">{{ $especialidad['rubro'] }} - {{ $especialidad['tipoServicio'] }}</small>
                            </div>
                            <span class="badge bg-info-subtle text-info">
                                {{ $especialidad['proveedores'] }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aun no hay especialidades asignadas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1">Actividad reciente</h5>
                    <p class="text-muted mb-0">Ultimos movimientos auditados.</p>
                </div>
                <div class="card-body inicio-admin-activity">
                    @forelse ($data['actividadReciente'] as $actividad)
                        <div class="inicio-activity-item">
                            <div class="inicio-activity-icon bg-success-subtle text-success">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $actividad->description }}</div>
                                <small class="text-muted">
                                    {{ $actividad->log_name }}
                                    @if ($actividad->causer)
                                        por {{ $actividad->causer->name }}
                                    @endif
                                </small>
                            </div>
                            <small class="text-muted text-nowrap">
                                {{ $actividad->created_at->diffForHumans() }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aun no hay actividad registrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
