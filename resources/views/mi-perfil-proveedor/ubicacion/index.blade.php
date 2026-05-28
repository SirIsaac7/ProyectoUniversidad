@extends('layouts.app')

@section('title', 'Mi ubicacion')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mi ubicacion</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

@php
    $latitudMapa = old('latitud', $ubicacion?->latitud ?? '-16.5000000');
    $longitudMapa = old('longitud', $ubicacion?->longitud ?? '-68.1500000');
    $radioMapa = old('radio_cobertura_km', $radioCobertura);
@endphp

<div class="mi-ubicacion-dashboard">
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-4">
            <div class="card mi-ubicacion-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-ubicacion-stat-icon bg-primary-subtle text-primary">
                            <i class="ri-map-pin-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Punto de atención</p>
                            <h5 class="mb-1">{{ $ubicacion?->direccion ?: 'Sin direccion registrada' }}</h5>
                            <small class="text-muted">{{ $ubicacion?->zona ?: 'La Paz, Bolivia' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card mi-ubicacion-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-ubicacion-stat-icon bg-success-subtle text-success">
                            <i class="ri-focus-3-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Zona de servicio</p>
                            <h5 class="mb-1">{{ $tieneUbicacion ? ($ubicacion?->zona ?: 'Zona no especificada') : 'Pendiente' }}</h5>
                            <small class="text-muted">{{ $tieneUbicacion ? 'Radio de '.$radioCobertura.' km' : 'Agrega tu punto de atencion' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card mi-ubicacion-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-ubicacion-stat-icon bg-info-subtle text-info">
                            <i class="ri-time-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Ultima actualización</p>
                            <h5 class="mb-1">{{ $ubicacion?->updated_at?->format('d/m/Y') ?? 'Sin registro' }}</h5>
                            <small class="text-muted">{{ $ubicacion?->updated_at?->diffForHumans() ?? 'Aun no configurada' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card h-100 mi-ubicacion-map-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="card-title mb-0">Ubicación en el mapa</h5>

                        @can('gestionar ubicacion proveedor')
                            <button type="button" class="btn btn-primary js-mi-ubicacion-form-toggle">
                                <i class="{{ $tieneUbicacion ? 'ri-pencil-line' : 'ri-add-line' }} align-bottom me-1"></i>
                                {{ $tieneUbicacion ? 'Editar ubicación' : 'Agregar ubicación' }}
                            </button>
                        @endcan
                    </div>

                    <div
                        id="miUbicacionProveedorMap"
                        class="ubicacion-proveedor-map mi-ubicacion-map flex-grow-1"
                        data-lat="{{ $latitudMapa }}"
                        data-lng="{{ $longitudMapa }}"
                        data-radio="{{ $radioMapa }}"
                        data-use-current-location="false"
                    ></div>

                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mi-ubicacion-side-card">
                <div class="card-body">
                    <div id="miUbicacionSummaryPanel" class="{{ $errors->any() ? 'd-none' : '' }}">
                        <h5 class="card-title mb-3">Resumen de cobertura</h5>

                        <div class="vstack gap-3">
                            <div class="mi-ubicacion-summary-item">
                                <span class="mi-ubicacion-summary-icon bg-success-subtle text-success">
                                    <i class="ri-checkbox-circle-line"></i>
                                </span>
                                <div>
                                    <small class="text-muted d-block">Estado</small>
                                    <span class="fw-semibold">{{ $tieneUbicacion ? 'Ubicacion configurada' : 'Pendiente de configurar' }}</span>
                                </div>
                            </div>

                            <div class="mi-ubicacion-summary-item">
                                <span class="mi-ubicacion-summary-icon bg-primary-subtle text-primary">
                                    <i class="ri-map-pin-line"></i>
                                </span>
                                <div>
                                    <small class="text-muted d-block">Zona principal</small>
                                    <span class="fw-semibold">{{ $ubicacion?->zona ?: 'Sin zona registrada' }}</span>
                                </div>
                            </div>

                            <div class="mi-ubicacion-summary-item">
                                <span class="mi-ubicacion-summary-icon bg-info-subtle text-info">
                                    <i class="ri-radar-line"></i>
                                </span>
                                <div>
                                    <small class="text-muted d-block">Radio de cobertura</small>
                                    <span class="fw-semibold">{{ $radioCobertura }} km alrededor del punto</span>
                                </div>
                            </div>

                            <div class="mi-ubicacion-summary-address">
                                <small class="text-muted d-block mb-1">Direccion</small>
                                <p class="mb-0">{{ $ubicacion?->direccion ?: 'Aun no registraste una direccion visible para clientes.' }}</p>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <span class="badge bg-info-subtle text-info">Ciudad: La Paz</span>
                                    <span class="badge {{ $ubicacion?->zona ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
                                        Barrio: {{ $ubicacion?->zona ?: 'Sin zona' }}
                                    </span>
                                    <span class="badge bg-success-subtle text-success">Cobertura: {{ $radioCobertura }} km</span>
                                </div>
                            </div>

                            <div class="mi-ubicacion-inline-tip">
                                <div class="d-flex gap-3">
                                    <span class="mi-ubicacion-tip-icon">
                                        <i class="ri-lightbulb-line"></i>
                                    </span>
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary mb-2">Consejo</span>
                                        <p class="mb-0">Manten tu ubicacion actualizada para que los clientes sepan si puedes atender su zona.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('gestionar ubicacion proveedor')
                        @if ($tieneUbicacion)
                            @include('mi-perfil-proveedor.ubicacion.partials.edit')
                        @else
                            @include('mi-perfil-proveedor.ubicacion.partials.create')
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/ubicacionesProveedor.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/miPerfilProveedorUbicacion.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedorUbicacion.js') }}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMiPerfilProveedorUbicacionMap"></script>
@endpush
