@extends('layouts.app')

@section('title', 'Mis solicitudes')

@push('styles')
<link href="{{ asset('assets/css/solicitudes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
@php
    $solicitudesColeccion = $solicitudes->getCollection();
    $solicitudInicial = $solicitudesColeccion->first();

    $estadoMeta = [
        'pendiente' => ['label' => 'Pendiente', 'class' => 'warning', 'icon' => 'ri-time-line'],
        'aceptada' => ['label' => 'Aceptada', 'class' => 'success', 'icon' => 'ri-checkbox-circle-line'],
        'rechazada' => ['label' => 'Rechazada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
        'cancelada' => ['label' => 'Cancelada', 'class' => 'danger', 'icon' => 'ri-close-circle-line'],
        'en_proceso' => ['label' => 'En proceso', 'class' => 'info', 'icon' => 'ri-loader-2-line'],
        'finalizada' => ['label' => 'Completada', 'class' => 'primary', 'icon' => 'ri-flag-line'],
    ];

    $estadisticas = [
        [
            'titulo' => 'Pendientes',
            'valor' => $resumenSolicitudes['pendientes'],
            'texto' => 'En espera de respuesta',
            'icono' => 'ri-time-line',
            'color' => 'warning',
        ],
        [
            'titulo' => 'Aceptadas',
            'valor' => $resumenSolicitudes['aceptadas'],
            'texto' => 'Solicitud aceptada',
            'icono' => 'ri-calendar-check-line',
            'color' => 'info',
        ],
        [
            'titulo' => 'En proceso',
            'valor' => $resumenSolicitudes['enProceso'],
            'texto' => 'Servicio en curso',
            'icono' => 'ri-tools-line',
            'color' => 'success',
        ],
        [
            'titulo' => 'Completadas',
            'valor' => $resumenSolicitudes['completadas'],
            'texto' => 'Servicios finalizados',
            'icono' => 'ri-calendar-check-line',
            'color' => 'primary',
        ],
    ];
@endphp

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
                        <button type="button" class="btn btn-primary js-solicitud-panel-toggle" data-panel-target="solicitudCreatePanel">
                            <i class="ri-add-line align-bottom me-1"></i>
                            Nueva solicitud
                        </button>
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
                @forelse ($solicitudes as $solicitud)
                    @php
                        $meta = $estadoMeta[$solicitud->estado] ?? ['label' => ucfirst(str_replace('_', ' ', $solicitud->estado)), 'class' => 'secondary', 'icon' => 'ri-information-line'];
                        $fechaTexto = $solicitud->fecha_solicitada
                            ? $solicitud->fecha_solicitada->format('d/m/Y')
                            : 'Sin fecha';
                        $horaTexto = $solicitud->hora_solicitada?->format('H:i') ?: 'Sin hora';
                        $rubro = $solicitud->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro';
                        $tipoServicio = $solicitud->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo';
                        $especialidad = $solicitud->especialidad?->nombre ?? 'Sin especialidad';
                        $proveedor = $solicitud->perfilProveedor?->nombre_publico ?? 'Sin proveedor';
                        $ubicacion = trim(($solicitud->zona ?: 'Sin zona') . ($solicitud->direccion ? ', ' . $solicitud->direccion : ''));
                    @endphp

                    <div
                        class="card solicitud-client-card js-solicitud-cliente-item"
                        data-estado="{{ $solicitud->estado }}"
                        data-created="{{ optional($solicitud->created_at)->timestamp ?? 0 }}"
                        data-search="{{ Str::lower($solicitud->titulo . ' ' . $solicitud->descripcion . ' ' . $proveedor . ' ' . $especialidad . ' ' . $rubro . ' ' . $tipoServicio . ' ' . $ubicacion) }}"
                    >
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="solicitud-client-thumb">
                                            <i class="ri-customer-service-2-line"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h5 class="mb-1 text-truncate">{{ $solicitud->titulo }}</h5>
                                            <p class="text-muted mb-2 text-truncate">{{ $especialidad }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar-xs">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                        <i class="ri-user-star-line"></i>
                                                    </span>
                                                </span>
                                                <small class="text-muted text-truncate">Proveedor: {{ $proveedor }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3">
                                    <div class="solicitud-client-meta">
                                        <i class="ri-calendar-line text-muted"></i>
                                        <div>
                                            <small class="text-muted d-block">Fecha solicitada</small>
                                            <span class="fw-semibold">{{ $fechaTexto }}</span>
                                            <small class="text-muted d-block">{{ $horaTexto }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-2">
                                    <div class="solicitud-client-meta">
                                        <i class="ri-map-pin-line text-muted"></i>
                                        <div>
                                            <small class="text-muted d-block">Ubicacion</small>
                                            <span class="fw-semibold">{{ $solicitud->zona ?: 'Sin zona' }}</span>
                                            <small class="text-muted d-block text-truncate">{{ $solicitud->direccion ?: 'Sin direccion' }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
                                        <span class="badge bg-{{ $meta['class'] }}-subtle text-{{ $meta['class'] }} solicitud-status-badge">
                                            <i class="{{ $meta['icon'] }} me-1"></i>
                                            {{ $meta['label'] }}
                                        </span>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-soft-info js-solicitud-detail-button"
                                            title="Ver detalle"
                                            data-id="{{ $solicitud->id }}"
                                            data-titulo="{{ e($solicitud->titulo) }}"
                                            data-estado="{{ $meta['label'] }}"
                                            data-estado-class="{{ $meta['class'] }}"
                                            data-estado-icon="{{ $meta['icon'] }}"
                                            data-descripcion="{{ e($solicitud->descripcion) }}"
                                            data-proveedor="{{ e($proveedor) }}"
                                            data-especialidad="{{ e($especialidad) }}"
                                            data-rubro="{{ e($rubro) }}"
                                            data-tipo-servicio="{{ e($tipoServicio) }}"
                                            data-tipo-atencion="{{ e(ucfirst(str_replace('_', ' ', $solicitud->tipo_atencion))) }}"
                                            data-fecha="{{ e($fechaTexto) }}"
                                            data-hora="{{ e($horaTexto) }}"
                                            data-zona="{{ e($solicitud->zona ?: 'Sin zona') }}"
                                            data-direccion="{{ e($solicitud->direccion ?: 'Sin direccion') }}"
                                        >
                                            <i class="ri-eye-line align-bottom"></i>
                                        </button>

                                        @can('editar mis solicitudes')
                                            @if ($solicitud->estado === 'pendiente')
                                                <button type="button" class="btn btn-sm btn-soft-warning js-solicitud-panel-toggle" data-panel-target="solicitudEditPanel{{ $solicitud->id }}" title="Editar">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </button>
                                            @endif
                                        @endcan

                                        @can('cancelar mis solicitudes')
                                            @if (! in_array($solicitud->estado, ['cancelada', 'finalizada'], true))
                                                <form method="POST" action="{{ route('cliente.solicitudes.destroy', $solicitud->id) }}" class="d-inline js-confirm-submit" data-confirm-title="Cancelar solicitud" data-confirm-text="Se cancelara esta solicitud.">
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
                    @if ($solicitudInicial)
                        @php
                            $metaInicial = $estadoMeta[$solicitudInicial->estado] ?? ['label' => ucfirst(str_replace('_', ' ', $solicitudInicial->estado)), 'class' => 'secondary', 'icon' => 'ri-information-line'];
                            $rubroInicial = $solicitudInicial->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro';
                            $tipoInicial = $solicitudInicial->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo';
                            $especialidadInicial = $solicitudInicial->especialidad?->nombre ?? 'Sin especialidad';
                            $proveedorInicial = $solicitudInicial->perfilProveedor?->nombre_publico ?? 'Sin proveedor';
                        @endphp
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <h5 class="mb-2 js-detail-title">Solicitud #{{ $solicitudInicial->id }}</h5>
                                <span class="badge bg-{{ $metaInicial['class'] }}-subtle text-{{ $metaInicial['class'] }} js-detail-status">
                                    <i class="{{ $metaInicial['icon'] }} me-1"></i>
                                    {{ $metaInicial['label'] }}
                                </span>
                            </div>
                            <i class="ri-file-list-3-line text-muted fs-20"></i>
                        </div>

                        <h6 class="fw-semibold mb-3">Detalles de la solicitud</h6>

                        <div class="solicitud-detail-list">
                            <div class="solicitud-detail-row">
                                <i class="ri-customer-service-2-line"></i>
                                <span>Servicio</span>
                                <strong class="js-detail-service">{{ $solicitudInicial->titulo }}</strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-file-text-line"></i>
                                <span>Descripcion</span>
                                <strong class="js-detail-description">{{ $solicitudInicial->descripcion }}</strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-price-tag-3-line"></i>
                                <span>Categoria</span>
                                <strong class="js-detail-category">{{ $rubroInicial }} - {{ $tipoInicial }}</strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-tools-line"></i>
                                <span>Especialidad</span>
                                <strong class="js-detail-specialty">{{ $especialidadInicial }}</strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-user-star-line"></i>
                                <span>Proveedor</span>
                                <strong class="js-detail-provider">{{ $proveedorInicial }}</strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-calendar-line"></i>
                                <span>Fecha</span>
                                <strong class="js-detail-date">
                                    {{ $solicitudInicial->fecha_solicitada?->format('d/m/Y') ?: 'Sin fecha' }}
                                    ·
                                    {{ $solicitudInicial->hora_solicitada?->format('H:i') ?: 'Sin hora' }}
                                </strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-map-pin-line"></i>
                                <span>Ubicacion</span>
                                <strong class="js-detail-location">
                                    {{ $solicitudInicial->zona ?: 'Sin zona' }}
                                    ·
                                    {{ $solicitudInicial->direccion ?: 'Sin direccion' }}
                                </strong>
                            </div>
                            <div class="solicitud-detail-row">
                                <i class="ri-hand-heart-line"></i>
                                <span>Atencion</span>
                                <strong class="js-detail-attention">{{ ucfirst(str_replace('_', ' ', $solicitudInicial->tipo_atencion)) }}</strong>
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
@endsection

@push('scripts')
<script src="{{ asset('assets/js/solicitudes.js') }}"></script>
@endpush
