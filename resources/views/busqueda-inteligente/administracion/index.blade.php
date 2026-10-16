@extends('layouts.app')

@section('title', 'Motor de busqueda inteligente')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div
    id="motorBusquedaPanel"
    data-estado-url="{{ route('motor-busqueda.estado') }}"
    data-indexacion-url="{{ route('motor-busqueda.indexacion.store') }}"
    data-seguimiento-url="{{ route('motor-busqueda.indexacion.show', ['identificador' => '__IDENTIFICADOR__']) }}"
    data-identificador-activo="{{ $identificadorActivo }}"
    data-puede-consultar="@can('consultar estado motor busqueda')1 @else 0 @endcan"
    data-puede-indexar="@can('ejecutar indexacion motor busqueda')1 @else 0 @endcan"
>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Motor de busqueda inteligente</h4>
                    <p class="text-muted mb-0">Supervisa los servicios y controla la indexación manual de proveedores.</p>
                </div>

                @can('consultar estado motor busqueda')
                    <button type="button" class="btn btn-soft-primary" id="actualizarEstadoMotor">
                        <i class="ri-refresh-line align-bottom me-1"></i>
                        Actualizar estados
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <div id="motorMensaje" class="alert d-none" role="alert" aria-live="polite"></div>

    <section class="card motor-overview-card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="motor-overview-icon"><i class="ri-brain-line"></i></span>
                    <div>
                        <span class="text-uppercase text-muted fw-semibold small">Estado general del motor</span>
                        <h3 class="mb-1" id="motorEstadoGeneral">Sin comprobar</h3>
                        <p class="text-muted mb-0" id="motorEstadoDescripcion">Actualiza los estados para comprobar las dependencias del motor.</p>
                    </div>
                </div>
                <span class="motor-status-pill is-unknown" id="motorEstadoGeneralBadge">
                    <span></span>Indeterminado
                </span>
            </div>
        </div>
    </section>

    <section class="mb-4" aria-labelledby="serviciosMotorTitulo">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0" id="serviciosMotorTitulo">Estado de los servicios</h5>
            <small class="text-muted" id="motorConsultadoEn">Sin actualizar</small>
        </div>
        <div class="row g-3">
            @foreach ([
                'fastapi' => ['FastAPI', 'ri-flashlight-line'],
                'mysql' => ['MySQL', 'ri-database-2-line'],
                'qdrant' => ['Qdrant', 'ri-focus-3-line'],
            ] as $clave => [$nombre, $icono])
                <div class="col-md-4">
                    <article class="card motor-service-card h-100" data-motor-service="{{ $clave }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <span class="motor-service-icon"><i class="{{ $icono }}"></i></span>
                                <span class="motor-status-pill is-unknown" data-service-badge><span></span>Sin comprobar</span>
                            </div>
                            <h5 class="mt-3 mb-1">{{ $nombre }}</h5>
                            <p class="text-muted mb-0" data-service-detail>No fue posible determinar su estado todavia.</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card mb-4" aria-labelledby="controlIndexacionTitulo">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="card-title mb-1" id="controlIndexacionTitulo">Indexación manual</h5>
                <p class="text-muted mb-0">Sincroniza manualmente los proveedores con el índice vectorial.</p>
            </div>
            @can('ejecutar indexacion motor busqueda')
                <button type="button" class="btn btn-primary" id="iniciarIndexacionMotor">
                    <i class="ri-play-circle-line align-bottom me-1"></i>
                    Iniciar indexación
                </button>
            @endcan
        </div>
        <div class="card-body">
            <div class="motor-indexing-note">
                <i class="ri-information-line"></i>
                <div>
                    <strong>Inicio manual controlado</strong>
                    <p class="mb-0">Este botón inicia una ejecución manual. Los cambios de proveedores nunca generan indexaciones por sí solos.</p>
                </div>
            </div>
        </div>
    </section>

    @can('configurar programacion indexaciones motor')
        <section class="card mb-4" aria-labelledby="programacionIndexacionTitulo">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1" id="programacionIndexacionTitulo">Programación de indexación</h5>
                        <p class="text-muted mb-0">Configura ejecuciones automáticas en la zona horaria America/La_Paz.</p>
                    </div>
                    <span class="badge {{ $configuracion->activo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                        {{ $configuracion->activo ? 'Programación activa' : 'Programación desactivada' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('motor-busqueda.programacion.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <div class="form-check form-switch motor-program-switch">
                                <input type="hidden" name="activo" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="programacionActiva" name="activo" value="1" @checked(old('activo', $configuracion->activo))>
                                <label class="form-check-label" for="programacionActiva">Indexación automática</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="frecuenciaIndexacion">Frecuencia</label>
                            <select class="form-select @error('frecuencia') is-invalid @enderror" id="frecuenciaIndexacion" name="frecuencia">
                                <option value="diario" @selected(old('frecuencia', $configuracion->frecuencia) === 'diario')>Diaria</option>
                                <option value="semanal" @selected(old('frecuencia', $configuracion->frecuencia) === 'semanal')>Semanal</option>
                            </select>
                            @error('frecuencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2" id="diaSemanaIndexacionWrapper">
                            <label class="form-label" for="diaSemanaIndexacion">Día</label>
                            <select class="form-select @error('dia_semana') is-invalid @enderror" id="diaSemanaIndexacion" name="dia_semana">
                                <option value="">Selecciona</option>
                                @foreach ([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $valor => $dia)
                                    <option value="{{ $valor }}" @selected((int) old('dia_semana', $configuracion->dia_semana) === $valor)>{{ $dia }}</option>
                                @endforeach
                            </select>
                            @error('dia_semana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="horaIndexacion">Hora</label>
                            <input type="time" class="form-control @error('hora_ejecucion') is-invalid @enderror" id="horaIndexacion" name="hora_ejecucion" value="{{ old('hora_ejecucion', substr($configuracion->hora_ejecucion, 0, 5)) }}" required>
                            @error('hora_ejecucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ri-save-line align-bottom me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="motor-next-run mt-3">
                    <i class="ri-calendar-schedule-line"></i>
                    <div>
                        <small>Próxima ejecución</small>
                        <strong>{{ $proximaEjecucion ? $proximaEjecucion->format('d/m/Y H:i') . ' (America/La_Paz)' : 'No programada' }}</strong>
                    </div>
                </div>
            </div>
        </section>
    @endcan

    @can('ver personalizacion busqueda')
        @include('busqueda-inteligente.administracion.partials.personalizacion')
    @endcan

    <section class="card mb-4 {{ $identificadorActivo ? '' : 'd-none' }}" id="seguimientoIndexacion" aria-labelledby="seguimientoIndexacionTitulo">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="card-title mb-1" id="seguimientoIndexacionTitulo">Seguimiento de la indexación</h5>
                <span class="text-muted small">Identificador: <code id="indexacionIdentificador">{{ $identificadorActivo ?: '—' }}</code></span>
            </div>
            <span class="motor-status-pill is-pending" id="indexacionEstadoBadge"><span></span>Pendiente</span>
        </div>
        <div class="card-body">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7">
                    <span class="text-uppercase text-muted fw-semibold small">Fase actual</span>
                    <h4 class="mt-1 mb-2" id="indexacionFase">Esperando información</h4>
                    <p class="text-muted mb-3" id="indexacionDescripcion">Consultando el progreso de la ejecución.</p>

                    <div class="progress motor-progress d-none" id="indexacionProgressWrapper" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" id="indexacionProgress" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between gap-3 mt-2 small text-muted">
                        <span id="indexacionConteo">Progreso no disponible</span>
                        <strong id="indexacionPorcentaje"></strong>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="motor-time-grid">
                        <div><small>Inicio</small><strong id="indexacionInicio">—</strong></div>
                        <div><small>Finalización</small><strong id="indexacionFin">—</strong></div>
                    </div>
                </div>
            </div>

            <div class="alert alert-danger d-none mt-4 mb-0" id="indexacionErrores"></div>
        </div>
    </section>

    <section class="mb-4 d-none" id="resultadosIndexacion" aria-labelledby="resultadosIndexacionTitulo">
        <h5 class="mb-3" id="resultadosIndexacionTitulo">Resultados de la ejecución</h5>
        <div class="row g-3" id="metricasIndexacion"></div>
    </section>

    <section class="card d-none" id="fasesIndexacion" aria-labelledby="fasesIndexacionTitulo">
        <div class="card-header">
            <h5 class="card-title mb-0" id="fasesIndexacionTitulo">Detalle de las fases</h5>
        </div>
        <div class="card-body">
            <div class="motor-timeline" id="listaFasesIndexacion"></div>
        </div>
    </section>

    @can('ver historial indexaciones motor')
        <section class="card mt-4" aria-labelledby="historialIndexacionesTitulo">
            <div class="card-header">
                <h5 class="card-title mb-0" id="historialIndexacionesTitulo">Historial de indexaciones</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('motor-busqueda.index') }}" class="row g-3 align-items-end mb-4">
                    <div class="col-md-3">
                        <label class="form-label" for="historialDesde">Desde</label>
                        <input type="date" class="form-control" id="historialDesde" name="fecha_desde" value="{{ $filtrosHistorial['fecha_desde'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="historialHasta">Hasta</label>
                        <input type="date" class="form-control @error('fecha_hasta') is-invalid @enderror" id="historialHasta" name="fecha_hasta" value="{{ $filtrosHistorial['fecha_hasta'] ?? '' }}">
                        @error('fecha_hasta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="historialTipo">Tipo</label>
                        <select class="form-select" id="historialTipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="manual" @selected(($filtrosHistorial['tipo'] ?? '') === 'manual')>Manual</option>
                            <option value="automatica" @selected(($filtrosHistorial['tipo'] ?? '') === 'automatica')>Automática</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="historialEstado">Estado</label>
                        <select class="form-select" id="historialEstado" name="estado">
                            <option value="">Todos</option>
                            @foreach (['pendiente' => 'Pendiente', 'ejecucion' => 'En ejecución', 'completado' => 'Completado', 'error' => 'Error', 'desconocido' => 'Desconocido'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected(($filtrosHistorial['estado'] ?? '') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-filter-3-line"></i></button>
                        <a href="{{ route('motor-busqueda.index') }}" class="btn btn-light"><i class="ri-refresh-line"></i></a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                                <th>Duración</th>
                                <th>Procesados</th>
                                <th>Resultado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historial as $ejecucion)
                                @php
                                    $duracion = $ejecucion->duracion_segundos;
                                    $badge = match ($ejecucion->categoria_estado) {
                                        'completado' => 'success',
                                        'error' => 'danger',
                                        'ejecucion' => 'info',
                                        'pendiente' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $ejecucion->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary text-capitalize">{{ $ejecucion->tipo }}</span></td>
                                    <td>{{ $ejecucion->tipo === 'automatica' ? 'Sistema' : ($ejecucion->usuario?->name ?? 'Usuario eliminado') }}</td>
                                    <td><span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ $ejecucion->estado }}</span></td>
                                    <td>{{ $duracion !== null ? sprintf('%02d:%02d:%02d', intdiv($duracion, 3600), intdiv($duracion % 3600, 60), $duracion % 60) : '—' }}</td>
                                    <td>{{ $ejecucion->proveedores_procesados ?? 0 }} / {{ $ejecucion->total_proveedores ?? '—' }}</td>
                                    <td>{{ $ejecucion->categoria_estado === 'completado' ? 'Correcto' : ($ejecucion->categoria_estado === 'error' ? 'Con errores' : 'En proceso') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('motor-busqueda.historial.show', $ejecucion) }}" class="btn btn-sm btn-soft-primary" title="Ver detalle">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5">Todavía no existen indexaciones registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($historial->hasPages())
                    <div class="mt-3">{{ $historial->links() }}</div>
                @endif
            </div>
        </section>
    @endcan
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/motorBusquedaAdmin.js') }}"></script>
@endpush
