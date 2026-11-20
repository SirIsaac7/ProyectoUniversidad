@extends('layouts.app')

@section('title', 'Indexación del motor inteligente')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div
    id="motorBusquedaPanel"
    data-indexacion-url="{{ route('motor-busqueda.indexacion.store') }}"
    data-seguimiento-url="{{ route('motor-busqueda.indexacion.show', ['identificador' => '__IDENTIFICADOR__']) }}"
    data-identificador-activo="{{ $identificadorActivo }}"
    data-puede-consultar="@can('consultar estado motor busqueda')1 @else 0 @endcan"
    data-puede-indexar="@can('ejecutar indexacion motor busqueda')1 @else 0 @endcan"
>
    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
        <div>
            <h4 class="mb-1">Indexación manual</h4>
            <p class="text-muted mb-0">Sincroniza manualmente los proveedores con el índice vectorial.</p>
        </div>
    </div>

    <div id="motorMensaje" class="alert d-none" role="alert" aria-live="polite"></div>

    <section class="card mb-4" aria-labelledby="controlIndexacionTitulo">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="card-title mb-1" id="controlIndexacionTitulo">Nueva indexación</h5>
                <p class="text-muted mb-0">Los cambios de proveedores nunca generan indexaciones por sí solos.</p>
            </div>
            @can('ejecutar indexacion motor busqueda')
                <button type="button" class="btn btn-primary" id="iniciarIndexacionMotor">
                    <i class="ri-play-circle-line align-bottom me-1"></i>Iniciar indexación
                </button>
            @endcan
        </div>
        <div class="card-body">
            <div class="motor-indexing-note">
                <i class="ri-information-line"></i>
                <div>
                    <strong>Inicio manual controlado</strong>
                    <p class="mb-0">Solo puede existir una indexación en curso.</p>
                </div>
            </div>
        </div>
    </section>

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
        <div class="card-header"><h5 class="card-title mb-0" id="fasesIndexacionTitulo">Detalle de las fases</h5></div>
        <div class="card-body"><div class="motor-timeline" id="listaFasesIndexacion"></div></div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/motorBusquedaAdmin.js') }}"></script>
@endpush
