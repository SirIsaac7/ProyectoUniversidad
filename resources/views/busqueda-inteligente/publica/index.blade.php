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
            <div
                id="busquedaSemanticaModulo"
                class="busqueda-semantica-scope is-card-{{ $personalizacionBusqueda['diseno_tarjetas'] }} is-spacing-{{ $personalizacionBusqueda['espaciado'] }} is-distribution-{{ $personalizacionBusqueda['distribucion'] }} is-rating-{{ $personalizacionBusqueda['apariencia_calificacion'] }} is-explanation-{{ $personalizacionBusqueda['apariencia_explicacion'] }}"
                style="{{ $personalizacionBusqueda['estilo'] }}"
            >
            <script type="application/json" id="busquedaSemanticaConfiguracion">@json($personalizacionBusqueda)</script>
            <div class="card busqueda-servicios-hero busqueda-semantica-hero mb-4">
                <div class="card-body">
                    <form
                        id="busquedaSemanticaForm"
                        data-url="{{ route('cliente.buscar-servicios.semantica') }}"
                        data-estado-url="{{ route('cliente.buscar-servicios.semantica.estado', ['ejecucion' => '__EJECUCION__']) }}"
                        data-resultados-url="{{ route('cliente.buscar-servicios.semantica.resultados', ['ejecucion' => '__EJECUCION__']) }}"
                        novalidate
                    >
                        <div class="mb-3">
                            <label for="consultaSemantica" class="form-label fw-semibold">Describe el problema de tu equipo tecnologico</label>
                            <div class="busqueda-semantica-input-group">
                                <i class="ri-search-line text-muted" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="consultaSemantica"
                                    name="consulta"
                                    class="form-control"
                                    maxlength="1000"
                                    required
                                    autocomplete="off"
                                    placeholder="Ej: Mi laptop enciende, pero la pantalla permanece negra"
                                >
                                <button
                                    type="button"
                                    class="btn btn-soft-primary busqueda-voz-button"
                                    id="btnDictarProblemaIA"
                                    data-url="{{ route('cliente.buscar-servicios.transcribir') }}"
                                    aria-controls="consultaSemantica"
                                >
                                    <i class="ri-mic-line align-bottom me-1"></i>
                                    Dictar
                                </button>
                                <button type="submit" class="btn btn-primary" id="btnBusquedaSemantica">
                                    <i class="ri-user-search-line align-bottom me-1"></i>
                                    Buscar tecnicos
                                </button>
                            </div>
                            <div class="invalid-feedback" data-busqueda-semantica-error></div>
                            <div id="estadoDictadoProblemaIA" class="form-text busqueda-voz-status" aria-live="polite">
                                Puedes escribir o usar el microfono para completar la descripcion.
                            </div>
                        </div>

                        <div
                            class="busqueda-imagen-gemini mb-3"
                            data-analisis-imagen
                            data-url="{{ route('cliente.buscar-servicios.analizar-imagen') }}"
                        >
                            <div class="busqueda-imagen-gemini-control">
                                <div>
                                    <label for="imagenProblemaGemini" class="form-label fw-semibold mb-1">
                                        ¿No sabes cómo describir el problema?
                                    </label>
                                    <p class="text-muted small mb-0">
                                        Selecciona una foto y el reconocimiento de imágenes completará la descripción.
                                    </p>
                                </div>
                                <label class="btn btn-soft-primary mb-0" for="imagenProblemaGemini">
                                    <i class="ri-image-add-line align-bottom me-1"></i>
                                    Elegir foto
                                </label>
                                <input
                                    type="file"
                                    id="imagenProblemaGemini"
                                    class="visually-hidden"
                                    accept="image/jpeg,image/png,image/webp"
                                    data-analisis-imagen-input
                                >
                            </div>

                            <div class="busqueda-imagen-gemini-preview d-none" data-analisis-imagen-preview>
                                <img src="" alt="Vista previa de la imagen seleccionada" data-analisis-imagen-thumb>
                                <div class="flex-grow-1 min-w-0">
                                    <strong class="d-block text-truncate" data-analisis-imagen-nombre></strong>
                                    <span class="small text-muted" data-analisis-imagen-estado aria-live="polite"></span>
                                </div>
                                <span class="spinner-border spinner-border-sm text-primary d-none" role="status" data-analisis-imagen-spinner></span>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <div class="form-check form-switch busqueda-semantica-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="soloDisponibles" name="disponible" value="1">
                                    <label class="form-check-label" for="soloDisponibles">Solo disponibles</label>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <label for="tipoAtencionSemantica" class="form-label">Tipo de atencion</label>
                                <select class="form-select" id="tipoAtencionSemantica" name="tipo_atencion">
                                    <option value="">Cualquier modalidad</option>
                                    <option value="domicilio">Atencion a domicilio</option>
                                    <option value="local">Atencion en local</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="busquedaSemanticaResultado" class="mb-4" aria-live="polite">
                <div class="card busqueda-semantica-empty">
                    <div class="card-body text-center py-4">
                        @if (is_file(public_path('assets/images/widget-service.png')))
                            <img
                                src="{{ asset('assets/images/widget-service.png') }}"
                                class="busqueda-empty-illustration mx-auto mb-3"
                                alt="Ilustración de búsqueda de técnicos"
                            >
                        @else
                            <span class="busqueda-empty-illustration-fallback mx-auto mb-3" aria-hidden="true">
                                <i class="ri-radar-line fs-24"></i>
                            </span>
                        @endif
                        <h5 class="mb-1">Encuentra al tecnico adecuado</h5>
                        <p class="text-muted mb-0">Describe el problema con tus propias palabras y compararemos tu necesidad con proveedores verificados.</p>
                    </div>
                </div>
            </div>
            </div>

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
