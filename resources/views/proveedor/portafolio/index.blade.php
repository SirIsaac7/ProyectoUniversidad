@extends('layouts.app')

@section('title', 'Mi portafolio')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mi portafolio</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="mi-portafolio-dashboard">
    <div class="row g-3 align-items-stretch mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card mi-portafolio-stat h-100">
                <div class="card-body">
                    <div class="mi-portafolio-stat-content">
                        <span class="mi-portafolio-stat-icon bg-primary-subtle text-primary">
                            <i class="ri-folder-image-line"></i>
                        </span>
                        <div>
                            <h3 class="mb-1">{{ $trabajosActivos->count() }}</h3>
                            <p class="text-muted fw-medium mb-1">Trabajos publicados</p>
                            <small class="text-muted">Activos en tu portafolio</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-portafolio-stat h-100">
                <div class="card-body">
                    <div class="mi-portafolio-stat-content">
                        <span class="mi-portafolio-stat-icon bg-success-subtle text-success">
                            <i class="ri-image-line"></i>
                        </span>
                        <div>
                            <h3 class="mb-1">{{ $imagenesTotales }}</h3>
                            <p class="text-muted fw-medium mb-1">Imagenes cargadas</p>
                            <small class="text-muted">Evidencias visibles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-portafolio-stat h-100">
                <div class="card-body">
                    <div class="mi-portafolio-stat-content">
                        <span class="mi-portafolio-stat-icon bg-info-subtle text-info">
                            <i class="ri-calendar-check-line"></i>
                        </span>
                        <div>
                            <h3 class="mb-1">{{ $trabajosConFecha }}</h3>
                            <p class="text-muted fw-medium mb-1">Con fecha</p>
                            <small class="text-muted">Trabajos documentados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-portafolio-stat h-100">
                <div class="card-body">
                    <div class="mi-portafolio-stat-content">
                        <span class="mi-portafolio-stat-icon bg-warning-subtle text-warning">
                            <i class="ri-time-line"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">{{ $ultimoTrabajo?->updated_at?->format('d/m/Y') ?? 'Sin registro' }}</h5>
                            <p class="text-muted fw-medium mb-1">Ultima actualizacion</p>
                            <small class="text-muted">{{ $ultimoTrabajo?->updated_at?->diffForHumans() ?? 'Aun sin trabajos' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mi-portafolio-main-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <h5 class="mb-1">Trabajos publicados</h5>
                            <p class="text-muted mb-0">Muestra evidencias reales de tus servicios realizados.</p>
                        </div>

                        @can('gestionar portafolio proveedor')
                            <button type="button" class="btn btn-primary mi-portafolio-add-btn js-mi-portafolio-panel-toggle" data-panel-target="miPortafolioCreatePanel">
                                <i class="ri-add-line align-bottom me-1"></i>
                                Nuevo trabajo
                            </button>
                        @endcan
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="mi-portafolio-tabs" role="tablist">
                            <button type="button" class="is-active" data-portafolio-tab="activos">
                                Todos los trabajos
                                <span class="badge bg-primary-subtle text-primary ms-1">{{ $trabajosActivos->count() }}</span>
                            </button>
                            <button type="button" data-portafolio-tab="inactivos">
                                Inactivos
                                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $trabajosInactivos->count() }}</span>
                            </button>
                        </div>

                        <div class="search-box mi-portafolio-search">
                            <input type="text" class="form-control search" id="miPortafolioSearch" placeholder="Buscar trabajos...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>

                    <div class="mi-portafolio-grid" id="miPortafolioGridActivos" data-portafolio-tab-panel="activos">
                        @forelse ($trabajosActivos as $trabajo)
                            @include('proveedor.portafolio.partials.trabajo')
                        @empty
                            <div class="mi-perfil-empty-state">
                                <span class="mi-perfil-empty-icon bg-primary-subtle text-primary">
                                    <i class="ri-gallery-upload-line"></i>
                                </span>
                                <h5>Aun no tienes trabajos activos</h5>
                                <p>Agrega evidencias de trabajos realizados para mostrar la calidad de tus servicios.</p>
                                @can('gestionar portafolio proveedor')
                                    <button type="button" class="btn btn-primary btn-sm js-mi-portafolio-panel-toggle" data-panel-target="miPortafolioCreatePanel">
                                        <i class="ri-add-line align-bottom me-1"></i>
                                        Nuevo trabajo
                                    </button>
                                @endcan
                            </div>
                        @endforelse
                    </div>

                    <div class="mi-portafolio-grid d-none" id="miPortafolioGridInactivos" data-portafolio-tab-panel="inactivos">
                        @forelse ($trabajosInactivos as $trabajo)
                            @include('proveedor.portafolio.partials.trabajo')
                        @empty
                            <div class="mi-perfil-empty-state">
                                <span class="mi-perfil-empty-icon bg-secondary-subtle text-secondary">
                                    <i class="ri-inbox-archive-line"></i>
                                </span>
                                <h5>No tienes trabajos inactivos</h5>
                                <p>Los trabajos que retires del portafolio apareceran aqui para poder revisarlos o activarlos despues.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mi-portafolio-empty-search d-none" id="miPortafolioEmptySearch">
                        <div class="mi-perfil-empty-state">
                            <span class="mi-perfil-empty-icon bg-info-subtle text-info">
                                <i class="ri-search-eye-line"></i>
                            </span>
                            <h5>Sin resultados</h5>
                            <p>No se encontraron trabajos con ese criterio de busqueda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @can('gestionar portafolio proveedor')
                @include('proveedor.portafolio.partials.create')

                @foreach ($trabajos as $trabajo)
                    @include('proveedor.portafolio.partials.edit')
                @endforeach
            @endcan

            <div class="mi-portafolio-side-placeholder {{ $errors->any() ? 'd-none' : '' }}" id="miPortafolioSidePlaceholder">
                <span class="mi-portafolio-panel-icon bg-primary-subtle text-primary">
                    <i class="ri-gallery-line"></i>
                </span>
                <h5 class="mt-3 mb-1">Gestiona tu portafolio</h5>
                <p class="text-muted mb-0">Usa el boton nuevo trabajo o el lapiz para editar desde aqui.</p>
            </div>
        </div>
    </div>

    <div class="alert mi-portafolio-tip mt-3 mb-0" role="alert">
        <div class="d-flex align-items-start gap-3">
            <span class="mi-portafolio-tip-icon">
                <i class="ri-lightbulb-line"></i>
            </span>
            <div>
                <span class="badge bg-primary-subtle text-primary mb-2">Consejo</span>
                <p class="mb-0">Sube fotos claras de trabajos reales para que los clientes entiendan la calidad de tu servicio.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/miPerfilProveedorPortafolio.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedorPortafolio.js') }}"></script>
@endpush
