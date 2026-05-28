@extends('layouts.app')

@section('title', 'Mis especialidades')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mis especialidades</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="mi-especialidades-dashboard">
    <div class="row g-3 align-items-stretch mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card mi-especialidad-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-especialidad-stat-icon bg-primary-subtle text-primary">
                            <i class="ri-dashboard-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Total especialidades</p>
                            <h3 class="mb-1">{{ $especialidadesActivas->count() }}</h3>
                            <small class="text-muted">Especialidades activas en tu perfil</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card mi-especialidad-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-especialidad-stat-icon bg-primary-subtle text-primary">
                            <i class="ri-star-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Principal asignada</p>
                            <h4 class="mb-1">
                                {{ $especialidadPrincipal?->especialidad?->nombre ?? 'Sin principal' }}
                            </h4>
                            <span class="badge bg-primary-subtle text-primary">{{ $especialidadPrincipal ? 'Principal' : 'Pendiente' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-especialidad-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-especialidad-stat-icon bg-success-subtle text-success">
                            <i class="ri-checkbox-circle-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Estado general</p>
                            <h4 class="mb-1">{{ $especialidadesAsignadas->where('estado', false)->count() ? 'Con inactivas' : 'Activa' }}</h4>
                            <small class="text-muted">
                                {{ $especialidadesAsignadas->where('estado', false)->count() ? 'Tienes especialidades inactivas' : 'Todas tus especialidades estan activas' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-2 d-flex align-items-center justify-content-xl-end">
            @can('gestionar especialidades proveedor')
                <button type="button" class="btn btn-primary mi-especialidad-add-btn js-mi-especialidad-panel-toggle" data-panel-target="miEspecialidadCreatePanel">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Agregar especialidad
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div class="mi-especialidad-tabs">
                            <span>Mis especialidades</span>
                        </div>

                        <div class="search-box mi-especialidad-search">
                            <input type="text" class="form-control search" id="miEspecialidadSearch" placeholder="Buscar especialidad...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>

                    <div class="vstack gap-3 mi-especialidades-list" id="miEspecialidadesList">
                        @forelse ($especialidadesAsignadas as $proveedorEspecialidad)
                            @php
                                $especialidad = $proveedorEspecialidad->especialidad;
                                $rubro = $especialidad?->rubroTipoServicio?->rubro?->nombre;
                                $tipoServicio = $especialidad?->rubroTipoServicio?->tipoServicio?->nombre;
                            @endphp
                            <div
                                class="mi-especialidad-card {{ ! $proveedorEspecialidad->estado ? 'is-inactive' : '' }}"
                                data-search-text="{{ strtolower($rubro . ' ' . $tipoServicio . ' ' . $especialidad?->nombre . ' ' . $especialidad?->descripcion) }}"
                            >
                            <div class="mi-especialidad-card-main">
                                <div>
                                    <h5 class="mb-2">{{ $rubro }} - {{ $tipoServicio }} - {{ $especialidad?->nombre }}</h5>
                                    @if ($proveedorEspecialidad->es_principal)
                                        <span class="badge bg-primary-subtle text-primary">Principal</span>
                                    @endif
                                </div>
                            </div>

                                <div class="mi-especialidad-card-description">
                                    <p class="mb-2">{{ $especialidad?->descripcion ?: 'Sin descripcion registrada.' }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if ($tipoServicio)
                                            <span class="badge bg-info-subtle text-info">{{ $tipoServicio }}</span>
                                        @endif
                                        @if ($especialidad?->nombre)
                                            <span class="badge bg-primary-subtle text-primary">{{ $especialidad->nombre }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mi-especialidad-card-status">
                                    @if ($proveedorEspecialidad->estado)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ri-checkbox-blank-circle-fill me-1"></i>
                                            Activa
                                        </span>
                                        <small class="text-muted d-block mt-2">Visible en tu perfil</small>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <i class="ri-checkbox-blank-circle-fill me-1"></i>
                                            Inactiva
                                        </span>
                                        <small class="text-muted d-block mt-2">No visible en tu perfil</small>
                                    @endif
                                </div>

                                <div class="mi-especialidad-card-actions">
                                    @can('gestionar especialidades proveedor')
                                        <button
                                            type="button"
                                            class="btn btn-sm mi-especialidad-edit-btn js-mi-especialidad-panel-toggle"
                                            data-panel-target="miEspecialidadEditPanel{{ $proveedorEspecialidad->id }}"
                                            title="Editar"
                                        >
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        @if ($proveedorEspecialidad->estado)
                                            <form method="POST" action="{{ route('mi-perfil-proveedor.especialidades.destroy', $proveedorEspecialidad->id) }}" class="js-confirm-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm mi-especialidad-delete-btn" title="Eliminar">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('mi-perfil-proveedor.especialidades.activar', $proveedorEspecialidad->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm mi-especialidad-activate-btn" title="Activar">
                                                    <i class="ri-refresh-line"></i>
                                                </button>
                                            </form>
                                        @endif

                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Aun no tienes especialidades registradas.</div>
                        @endforelse
                    </div>
                </div>

                <div class="col-xl-4">
                    @include('mi-perfil-proveedor.especialidades.partials.create')

                    @foreach ($especialidadesAsignadas as $proveedorEspecialidad)
                        @include('mi-perfil-proveedor.especialidades.partials.edit')
                    @endforeach

                    <div class="mi-especialidad-side-placeholder" id="miEspecialidadSidePlaceholder">
                        <span class="mi-especialidad-panel-icon bg-primary-subtle text-primary">
                            <i class="ri-cursor-line"></i>
                        </span>
                        <h5 class="mt-3 mb-1">Gestiona tus especialidades</h5>
                        <p class="text-muted mb-0">Usa el boton agregar o el lapiz de una especialidad para editar aqui.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert mi-especialidad-tip mt-3 mb-0" role="alert">
        <div class="d-flex align-items-start gap-3">
            <span class="mi-especialidad-tip-icon">
                <i class="ri-information-line"></i>
            </span>
            <div>
                <span class="badge bg-primary-subtle text-primary mb-2">Consejo</span>
                <p class="mb-0">Selecciona una especialidad como principal para que se muestre primero en tu perfil publico y en los resultados de busqueda.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/miPerfilProveedorEspecialidades.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedorEspecialidades.js') }}"></script>
@endpush
