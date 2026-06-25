@extends('layouts.app')

@section('title', 'Buscar servicios')

@push('styles')
<link href="{{ asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/mapaLaPaz.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/busquedaServicios.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="busqueda-servicios-cliente">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1 busqueda-servicios-title">Encuentra al proveedor</h4>
                    <p class="text-muted mb-0">Describe lo que necesitas y te ayudamos a encontrar al mejor profesional cerca de ti.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card busqueda-servicios-hero mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('cliente.buscar-servicios.index') }}">
                        <input type="hidden" name="rubro_id" value="{{ $filtros['rubro_id'] }}">
                        <input type="hidden" name="tipo_servicio_id" value="{{ $filtros['tipo_servicio_id'] }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-7">
                                <label class="form-label">Que necesitas?</label>
                                <div class="position-relative">
                                    <input
                                        type="text"
                                        name="q"
                                        value="{{ $filtros['q'] }}"
                                        class="form-control ps-4"
                                        placeholder="Ej: Reparacion de laptops, instalacion de camaras, plomeria..."
                                    >
                                    <i class="ri-search-line busqueda-servicios-search-icon text-muted"></i>
                                </div>
                            </div>

                            <div class="col-sm-7 col-lg-3">
                                <div class="form-check form-switch busqueda-location-switch mb-0">
                                    <input
                                        class="form-check-input js-busqueda-location-switch"
                                        type="checkbox"
                                        role="switch"
                                        id="usar_ubicacion_actual"
                                        name="usar_ubicacion_actual"
                                        value="1"
                                        @checked($filtros['usar_ubicacion_actual'])
                                    >
                                    <label class="form-check-label" for="usar_ubicacion_actual">
                                        Usar mi ubicacion actual
                                    </label>
                                </div>
                                <input type="hidden" name="latitud" id="busquedaLatitud" value="{{ $filtros['latitud'] }}">
                                <input type="hidden" name="longitud" id="busquedaLongitud" value="{{ $filtros['longitud'] }}">
                            </div>

                            <div class="col-sm-5 col-lg-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-search-line align-bottom me-1"></i>
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card busqueda-ia-panel mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2">
                                <i class="ri-sparkling-2-line align-bottom me-1"></i>
                                Modo IA
                            </span>
                            <h5 class="mb-1">Busqueda inteligente por imagen y descripcion</h5>
                            <p class="text-muted mb-0">Sube una foto del equipo o problema y explica que sucede. La IA sugerira proveedores compatibles.</p>
                        </div>
                        <span class="avatar-md">
                            <span class="avatar-title rounded bg-primary-subtle text-primary">
                                <i class="ri-brain-line fs-24"></i>
                            </span>
                        </span>
                    </div>

                    <form id="busquedaInteligenteForm" data-url="{{ route('cliente.buscar-servicios.inteligente') }}" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-lg-5">
                                <label for="imagenIA" class="form-label">Imagen del problema <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="imagenIA" name="imagen" accept=".jpg,.jpeg,.png,.webp" required>
                                <div class="form-text">Formatos permitidos: JPG, PNG o WEBP. Maximo 8MB.</div>
                            </div>

                            <div class="col-lg-7">
                                <label for="descripcionProblemaIA" class="form-label">Descripcion del problema <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="descripcionProblemaIA" name="texto_problema" rows="3" minlength="10" maxlength="1000" required placeholder="Ej: Mi laptop enciende, pero despues de unos minutos se apaga y hace ruido el ventilador."></textarea>
                            </div>

                            <div class="col-md-4">
                                <label for="modoClasificacionIA" class="form-label">Modo de clasificacion</label>
                                <select class="form-select" id="modoClasificacionIA" name="modo_clasificacion">
                                    <option value="cnn">CNN</option>
                                    <option value="gemini">PRO</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Ubicacion</label>
                                <div class="form-check form-switch busqueda-location-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="usarUbicacionIA" name="usar_ubicacion" value="1">
                                    <label class="form-check-label" for="usarUbicacionIA">Usar mi ubicacion actual</label>
                                </div>
                                <input type="hidden" id="latClienteIA" name="lat_cliente">
                                <input type="hidden" id="lonClienteIA" name="lon_cliente">
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="btnBusquedaInteligente">
                                    <i class="ri-search-eye-line align-bottom me-1"></i>
                                    Buscar con IA
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="busquedaInteligenteResultado" class="mb-4"></div>

            <livewire:cliente.busqueda-servicios.bloque-busqueda :filtros="$filtros" />
        </div>

        <div class="col-xl-4">
            <div class="card proveedores-cerca-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Proveedores cerca de ti</h5>
                    <i class="ri-refresh-line text-primary"></i>
                </div>
                <div class="card-body">
                    <div
                        class="proveedores-cerca-map-wrapper mb-3 {{ $filtros['usar_ubicacion_actual'] ? 'is-location-enabled' : '' }}"
                    >
                        <div
                            id="busquedaServiciosMap"
                            class="proveedores-cerca-map"
                            data-lat="{{ $filtros['latitud'] ?: '-16.5000000' }}"
                            data-lng="{{ $filtros['longitud'] ?: '-68.1500000' }}"
                        ></div>
                        <div class="proveedores-cerca-map-placeholder">
                            <span class="avatar-md mb-2">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    <i class="ri-map-pin-user-line fs-24"></i>
                                </span>
                            </span>
                            <p class="mb-1 fw-semibold">Activa tu ubicacion</p>
                            <small>Usa el switch del buscador para ver proveedores cercanos.</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-xs">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                <i class="ri-map-pin-line"></i>
                            </span>
                        </span>
                        <div>
                            <div class="fw-semibold js-busqueda-location-label">
                                {{ $filtros['usar_ubicacion_actual'] ? 'Mi ubicacion actual' : 'La Paz' }}
                            </div>
                            <small class="text-muted">Punto de referencia para la busqueda</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card busqueda-ayuda-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="avatar-md">
                            <span class="avatar-title rounded bg-primary-subtle text-primary">
                                <i class="ri-questionnaire-line fs-24"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="mb-2">No sabes que servicio necesitas?</h5>
                            <p class="text-muted mb-3">Describe tu problema al proveedor cuando envies la solicitud.</p>
                            <a href="{{ route('cliente.solicitudes.index') }}" class="btn btn-primary">
                                Ver mis solicitudes
                                <i class="ri-arrow-right-line align-bottom ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any() && old('origen') === 'busqueda_servicios' && old('perfil_proveedor_id'))
    <script>
        window.busquedaSolicitudModalProveedorId = @json(old('perfil_proveedor_id'));
    </script>
@endif
<script src="{{ asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/maps/LaPaz/datos-lapaz.js') }}"></script>
<script src="{{ asset('assets/js/maps/LaPaz/app.js') }}"></script>
<script src="{{ asset('assets/js/busquedaServicios.js') }}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initBusquedaServiciosMap"></script>
@endpush
