@extends('layouts.app')

@section('title', 'Estado del motor inteligente')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div
    id="motorBusquedaPanel"
    data-estado-url="{{ route('motor-busqueda.estado') }}"
    data-puede-consultar="@can('consultar estado motor busqueda')1 @else 0 @endcan"
>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Resumen del motor inteligente</h4>
                    <p class="text-muted mb-0">Supervisa la disponibilidad de FastAPI y sus dependencias.</p>
                </div>

                @can('consultar estado motor busqueda')
                    <button type="button" class="btn btn-soft-primary" id="actualizarEstadoMotor">
                        <i class="ri-refresh-line align-bottom me-1"></i>Actualizar estados
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
                <span class="motor-status-pill is-unknown" id="motorEstadoGeneralBadge"><span></span>Indeterminado</span>
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
                'fastapi' => ['FastAPI', 'FastApi.png'],
                'mysql' => ['MySQL', 'base-de-datos.png'],
                'qdrant' => ['Qdrant', 'qdrant.png'],
            ] as $clave => [$nombre, $imagen])
                <div class="col-md-4">
                    <article class="card motor-service-card h-100" data-motor-service="{{ $clave }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                @if (is_file(public_path('assets/images/'.$imagen)))
                                    <span class="motor-service-image">
                                        <img src="{{ asset('assets/images/'.$imagen) }}" alt="Logo de {{ $nombre }}">
                                    </span>
                                @endif
                                <span class="motor-status-pill is-unknown ms-auto" data-service-badge><span></span>Sin comprobar</span>
                            </div>
                            <h5 class="mt-3 mb-1">{{ $nombre }}</h5>
                            <p class="text-muted mb-0" data-service-detail>No fue posible determinar su estado todavía.</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/motorBusquedaAdmin.js') }}"></script>
@endpush
