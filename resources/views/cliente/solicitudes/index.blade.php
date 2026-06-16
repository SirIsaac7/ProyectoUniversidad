@extends('layouts.app')

@section('title', 'Mis solicitudes')

@push('styles')
<link href="{{ asset('assets/css/solicitudes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="solicitudes-cliente">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Mis solicitudes</h4>
                    <p class="text-muted mb-0">Aqui puedes ver todas las solicitudes que has realizado.</p>
                </div>

                <div class="page-title-right">
                    @can('crear mis solicitudes')
                        <a href="{{ route('cliente.buscar-servicios.index') }}" class="btn btn-primary">
                            <i class="ri-search-line align-bottom me-1"></i>
                            Buscar servicios
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="d-none" id="solicitudes-success-message" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        @foreach ($estadisticas as $estadistica)
            <div class="col-sm-6 col-xl-3">
                <div class="card solicitud-stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="solicitud-stat-icon bg-{{ $estadistica['color'] }}-subtle text-{{ $estadistica['color'] }}">
                                <i class="{{ $estadistica['icono'] }}"></i>
                            </span>
                            <div>
                                <h3 class="mb-1">{{ $estadistica['valor'] }}</h3>
                                <div class="fw-semibold text-body">{{ $estadistica['titulo'] }}</div>
                                <small class="text-muted">{{ $estadistica['texto'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <ul class="nav nav-tabs nav-tabs-custom nav-primary mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tabActivo === 'solicitudes' ? 'active' : '' }}" href="#clienteSolicitudesTab" data-bs-toggle="tab" role="tab">
                <i class="ri-file-list-2-line me-1"></i>
                Solicitudes
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tabActivo === 'citas' ? 'active' : '' }}" href="#clienteCitasTab" data-bs-toggle="tab" role="tab">
                <i class="ri-calendar-check-line me-1"></i>
                Citas
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tabActivo === 'historial' ? 'active' : '' }}" href="#clienteHistorialTab" data-bs-toggle="tab" role="tab">
                <i class="ri-history-line me-1"></i>
                Historial
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ $tabActivo === 'solicitudes' ? 'show active' : '' }}" id="clienteSolicitudesTab" role="tabpanel">
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card solicitudes-filter-card mb-3">
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-6">
                                    <div class="position-relative">
                                        <input type="text" class="form-control ps-4 js-solicitud-cliente-search" placeholder="Buscar por servicio, proveedor o especialidad...">
                                        <i class="ri-search-line solicitud-search-icon text-muted"></i>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <select class="form-select js-solicitud-cliente-estado">
                                        <option value="">Todos los estados</option>
                                        @foreach ($estadoMeta as $estado => $meta)
                                            <option value="{{ $estado }}">{{ $meta['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <select class="form-select js-solicitud-cliente-orden">
                                        <option value="recientes">Mas recientes</option>
                                        <option value="antiguas">Mas antiguas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="solicitudes-client-list">
                        @forelse ($solicitudesVista as $solicitudVista)
                            <div
                                class="card solicitud-client-card js-solicitud-cliente-item"
                                data-estado="{{ $solicitudVista['estado'] }}"
                                data-created="{{ $solicitudVista['created_timestamp'] }}"
                                data-search="{{ $solicitudVista['search'] }}"
                            >
                                <div class="card-body">
                                    <div class="solicitud-client-grid">
                                        <div class="solicitud-client-main">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="solicitud-client-thumb">
                                                    <i class="ri-customer-service-2-line"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="mb-1 text-truncate">{{ $solicitudVista['titulo'] }}</h5>
                                                    <p class="text-muted mb-2 text-truncate">{{ $solicitudVista['especialidad'] }}</p>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="avatar-xs">
                                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                                <i class="ri-user-star-line"></i>
                                                            </span>
                                                        </span>
                                                        <small class="text-muted text-truncate">Proveedor: {{ $solicitudVista['proveedor'] }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="solicitud-client-date">
                                            <div class="solicitud-client-meta">
                                                <i class="ri-calendar-line text-muted"></i>
                                                <div>
                                                    <small class="text-muted d-block">Fecha solicitada</small>
                                                    <span class="fw-semibold">{{ $solicitudVista['fecha_texto'] }}</span>
                                                    <small class="text-muted d-block">{{ $solicitudVista['hora_texto'] }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="solicitud-client-location">
                                            <div class="solicitud-client-meta">
                                                <i class="ri-map-pin-line text-muted"></i>
                                                <div>
                                                    <small class="text-muted d-block">Ubicacion</small>
                                                    <span class="fw-semibold">{{ $solicitudVista['zona'] }}</span>
                                                    <small class="text-muted d-block text-truncate">{{ $solicitudVista['direccion'] }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="solicitud-client-actions">
                                            <div class="solicitud-client-actions-inner">
                                                <span class="badge bg-{{ $solicitudVista['meta']['class'] }}-subtle text-{{ $solicitudVista['meta']['class'] }} solicitud-status-badge">
                                                    <i class="{{ $solicitudVista['meta']['icon'] }} me-1"></i>
                                                    {{ $solicitudVista['meta']['label'] }}
                                                </span>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-soft-info js-solicitud-detail-button"
                                                    title="Ver detalle"
                                                    data-id="{{ $solicitudVista['id'] }}"
                                                    data-titulo="{{ e($solicitudVista['titulo']) }}"
                                                    data-estado="{{ $solicitudVista['meta']['label'] }}"
                                                    data-estado-class="{{ $solicitudVista['meta']['class'] }}"
                                                    data-estado-icon="{{ $solicitudVista['meta']['icon'] }}"
                                                    data-descripcion="{{ e($solicitudVista['descripcion']) }}"
                                                    data-proveedor="{{ e($solicitudVista['proveedor']) }}"
                                                    data-especialidad="{{ e($solicitudVista['especialidad']) }}"
                                                    data-rubro="{{ e($solicitudVista['rubro']) }}"
                                                    data-tipo-servicio="{{ e($solicitudVista['tipo_servicio']) }}"
                                                    data-tipo-atencion="{{ e($solicitudVista['tipo_atencion']) }}"
                                                    data-fecha="{{ e($solicitudVista['fecha_texto']) }}"
                                                    data-hora="{{ e($solicitudVista['hora_texto']) }}"
                                                    data-zona="{{ e($solicitudVista['zona']) }}"
                                                    data-direccion="{{ e($solicitudVista['direccion']) }}"
                                                >
                                                    <i class="ri-eye-line align-bottom"></i>
                                                </button>

                                                @can('editar mis solicitudes')
                                                    @if ($solicitudVista['puede_editar'])
                                                        <button type="button" class="btn btn-sm btn-soft-warning js-solicitud-panel-toggle" data-panel-target="solicitudEditPanel{{ $solicitudVista['id'] }}" title="Editar">
                                                            <i class="ri-pencil-fill align-bottom"></i>
                                                        </button>
                                                    @endif
                                                @endcan

                                                @can('cancelar mis solicitudes')
                                                    @if ($solicitudVista['puede_cancelar'])
                                                        <form method="POST" action="{{ route('cliente.solicitudes.destroy', $solicitudVista['id']) }}" class="d-inline js-confirm-submit" data-confirm-title="Cancelar solicitud" data-confirm-text="Se cancelara esta solicitud.">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Cancelar">
                                                                <i class="ri-close-circle-line align-bottom"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <div class="avatar-md mx-auto mb-3">
                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                            <i class="ri-file-add-line fs-24"></i>
                                        </span>
                                    </div>
                                    <h5 class="mb-2">Aun no tienes solicitudes</h5>
                                    <p class="text-muted mb-0">Cuando solicites un servicio, aparecera aqui.</p>
                                </div>
                            </div>
                        @endforelse

                        <div class="card js-solicitud-cliente-empty d-none">
                            <div class="card-body text-center py-4 text-muted">
                                No se encontraron solicitudes con esos filtros.
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        {{ $solicitudes->links() }}
                    </div>
                </div>

                <div class="col-xl-4">
                    @include('cliente.solicitudes.partials.create')

                    @foreach ($solicitudesColeccion as $solicitud)
                        @include('cliente.solicitudes.partials.edit')
                    @endforeach

                    <div class="card solicitud-detail-panel sticky-xl-top solicitud-side-panel" id="solicitudDetailPanel">
                        <div class="card-body">
                            @if ($solicitudInicialDetalle)
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div>
                                        <h5 class="mb-2 js-detail-title">Solicitud #{{ $solicitudInicialDetalle['id'] }}</h5>
                                        <span class="badge bg-{{ $solicitudInicialDetalle['meta']['class'] }}-subtle text-{{ $solicitudInicialDetalle['meta']['class'] }} js-detail-status">
                                            <i class="{{ $solicitudInicialDetalle['meta']['icon'] }} me-1"></i>
                                            {{ $solicitudInicialDetalle['meta']['label'] }}
                                        </span>
                                    </div>
                                    <i class="ri-file-list-3-line text-muted fs-20"></i>
                                </div>

                                <h6 class="fw-semibold mb-3">Detalles de la solicitud</h6>

                                <div class="solicitud-detail-list">
                                    <div class="solicitud-detail-row">
                                        <i class="ri-customer-service-2-line"></i>
                                        <span>Servicio</span>
                                        <strong class="js-detail-service">{{ $solicitudInicialDetalle['titulo'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-file-text-line"></i>
                                        <span>Descripcion</span>
                                        <strong class="js-detail-description">{{ $solicitudInicialDetalle['descripcion'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-price-tag-3-line"></i>
                                        <span>Categoria</span>
                                        <strong class="js-detail-category">{{ $solicitudInicialDetalle['categoria'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-tools-line"></i>
                                        <span>Especialidad</span>
                                        <strong class="js-detail-specialty">{{ $solicitudInicialDetalle['especialidad'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-user-star-line"></i>
                                        <span>Proveedor</span>
                                        <strong class="js-detail-provider">{{ $solicitudInicialDetalle['proveedor'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-calendar-line"></i>
                                        <span>Fecha</span>
                                        <strong class="js-detail-date">{{ $solicitudInicialDetalle['fecha'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-map-pin-line"></i>
                                        <span>Ubicacion</span>
                                        <strong class="js-detail-location">{{ $solicitudInicialDetalle['ubicacion'] }}</strong>
                                    </div>
                                    <div class="solicitud-detail-row">
                                        <i class="ri-hand-heart-line"></i>
                                        <span>Atencion</span>
                                        <strong class="js-detail-attention">{{ $solicitudInicialDetalle['tipo_atencion'] }}</strong>
                                    </div>
                                </div>

                                <div class="alert alert-info bg-info-subtle border-info-subtle text-info mt-3 mb-0">
                                    <i class="ri-information-line me-1"></i>
                                    El detalle se actualiza al seleccionar una solicitud.
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="avatar-md mx-auto mb-3">
                                        <span class="avatar-title rounded-circle bg-light text-muted">
                                            <i class="ri-file-list-3-line fs-24"></i>
                                        </span>
                                    </div>
                                    <h5 class="mb-2">Sin detalle</h5>
                                    <p class="text-muted mb-0">Selecciona una solicitud para ver su informacion.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('cliente.citas.partials.tab-citas')
        @include('cliente.historial-solicitudes.partials.tab-historial')
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/solicitudes.js') }}"></script>
@endpush
