@extends('layouts.app')

@section('title', 'Mis documentos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mis documentos</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="mi-documentos-dashboard">
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card mi-documento-stat h-100">
                <div class="card-body">
                    <div class="mi-documento-stat-content">
                        <span class="mi-documento-stat-icon bg-primary-subtle text-primary">
                            <i class="ri-file-list-3-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Documentos cargados</p>
                            <h3 class="mb-1">{{ $documentos->count() }}</h3>
                            <small class="text-muted">Activos en tu perfil</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-documento-stat h-100">
                <div class="card-body">
                    <div class="mi-documento-stat-content">
                        <span class="mi-documento-stat-icon bg-success-subtle text-success">
                            <i class="ri-checkbox-circle-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Aprobados</p>
                            <h3 class="mb-1">{{ $documentosAprobados }}</h3>
                            <small class="text-muted">Validados por administracion</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-documento-stat h-100">
                <div class="card-body">
                    <div class="mi-documento-stat-content">
                        <span class="mi-documento-stat-icon bg-warning-subtle text-warning">
                            <i class="ri-time-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Pendientes</p>
                            <h3 class="mb-1">{{ $documentosPendientes }}</h3>
                            <small class="text-muted">Esperando revision</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-documento-stat h-100">
                <div class="card-body">
                    <div class="mi-documento-stat-content">
                        <span class="mi-documento-stat-icon {{ $documentosRechazados ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }}">
                            <i class="{{ $documentosRechazados ? 'ri-error-warning-line' : 'ri-calendar-check-line' }}"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">{{ $documentosRechazados ? 'Rechazados' : 'Ultima actualizacion' }}</p>
                            <h5 class="mb-1">{{ $documentosRechazados ?: ($ultimoDocumento?->updated_at?->format('d/m/Y') ?? 'Sin registro') }}</h5>
                            <small class="text-muted">{{ $documentosRechazados ? 'Revisa las observaciones' : ($ultimoDocumento?->updated_at?->diffForHumans() ?? 'Aun no subiste documentos') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mi-documentos-main-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h5 class="mb-0">Carpetas</h5>
                                <span class="badge mi-documento-folder-hint">
                                    Haz click en una carpeta para filtrar
                                </span>
                            </div>
                            <p class="text-muted mb-0">Tus documentos se agrupan segun su tipo.</p>
                        </div>
                        @can('gestionar documentos proveedor')
                            <button type="button" class="btn btn-primary mi-documento-upload-btn js-mi-documento-panel-toggle" data-panel-target="miDocumentoCreatePanel">
                                <i class="ri-upload-2-line align-bottom me-1"></i>
                                Subir documento
                            </button>
                        @endcan
                    </div>

                    <div class="mi-documentos-folder-wrapper mb-3" id="miDocumentosFolderWrapper">
                        @foreach ($carpetas as $carpeta)
                            <button type="button" class="mi-documento-folder" data-folder-card data-tipo-documento-id="{{ $carpeta['id'] }}">
                                <span class="mi-documento-folder-icon bg-{{ $carpeta['color'] }}-subtle text-{{ $carpeta['color'] }}">
                                    <i class="{{ $carpeta['icono'] }}"></i>
                                </span>
                                <div class="mi-documento-folder-content">
                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <h6 class="mb-0">{{ $carpeta['nombre'] }}</h6>
                                        <span
                                            class="mi-documento-folder-help"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ $carpeta['descripcion'] }}"
                                        >
                                            <i class="ri-information-line"></i>
                                        </span>
                                    </div>
                                    <span class="badge bg-light text-body mt-3">{{ $carpeta['documentos']->count() }} documentos</span>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div class="mi-documentos-folder-pagination d-none mb-4" id="miDocumentosFolderPagination">
                        <button type="button" class="btn btn-sm btn-soft-primary" data-folder-page-action="prev">
                            <i class="ri-arrow-left-s-line align-bottom"></i>
                        </button>
                        <span class="mi-documentos-folder-page-info" data-folder-page-info></span>
                        <button type="button" class="btn btn-sm btn-soft-primary" data-folder-page-action="next">
                            <i class="ri-arrow-right-s-line align-bottom"></i>
                        </button>
                    </div>

                    <div class="mi-documentos-list-card">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="mi-documentos-list-icon bg-primary-subtle text-primary">
                                    <i class="ri-folder-open-line"></i>
                                </span>
                                <h5 class="mb-0">Documentos registrados</h5>
                            </div>
                            <span class="badge bg-primary-subtle text-primary" id="miDocumentosRegistradosCount">{{ $documentos->count() }} documentos</span>
                        </div>

                        <div class="mi-documentos-table">
                            <div class="mi-documentos-table-head">
                                <span>Nombre</span>
                                <span>Tipo</span>
                                <span>Fecha de subida</span>
                                <span>Estado</span>
                                <span>Acciones</span>
                            </div>

                            <div class="mi-documentos-table-body">
                                @foreach ($documentos as $documento)
                                    @php
                                        $extension = strtolower(pathinfo($documento->archivo, PATHINFO_EXTENSION));
                                        $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']);
                                        $estadoClases = [
                                            'aprobado' => 'bg-success-subtle text-success',
                                            'rechazado' => 'bg-danger-subtle text-danger',
                                            'pendiente' => 'bg-warning-subtle text-warning',
                                        ];
                                    @endphp
                                    <div class="mi-documento-row" data-documento-row data-tipo-documento-id="{{ $documento->tipo_documento_proveedor_id }}">
                                        <div class="mi-documento-name">
                                            <span class="mi-documento-file-icon {{ $esImagen ? 'is-image' : 'is-pdf' }}">
                                                <i class="{{ $esImagen ? 'ri-image-line' : 'ri-file-pdf-2-line' }}"></i>
                                            </span>
                                            <div>
                                                <h6 class="mb-1">{{ $documento->tipoDocumentoProveedor?->nombre }}</h6>
                                                <small class="text-muted">{{ basename($documento->archivo) }}</small>
                                            </div>
                                        </div>

                                        <span class="text-muted">{{ strtoupper($extension ?: 'Archivo') }}</span>
                                        <span class="text-muted">{{ $documento->created_at?->format('d/m/Y H:i') }}</span>

                                        <span>
                                            <span class="badge {{ $estadoClases[$documento->estado_revision] ?? $estadoClases['pendiente'] }}">
                                                <i class="ri-checkbox-blank-circle-fill me-1"></i>
                                                {{ ucfirst($documento->estado_revision) }}
                                            </span>
                                        </span>

                                        <div class="mi-documento-actions">
                                            <a href="{{ asset($documento->archivo) }}" target="_blank" rel="noopener" class="btn btn-sm mi-documento-view-btn" title="Ver documento">
                                                <i class="ri-eye-line"></i>
                                            </a>

                                            @can('gestionar documentos proveedor')
                                                <button type="button" class="btn btn-sm mi-documento-edit-btn js-mi-documento-panel-toggle" data-panel-target="miDocumentoEditPanel{{ $documento->id }}" title="Editar">
                                                    <i class="ri-pencil-line"></i>
                                                </button>

                                                <form method="POST" action="{{ route('mi-perfil-proveedor.documentos.destroy', $documento->id) }}" class="js-confirm-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm mi-documento-delete-btn" title="Eliminar">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach

                                @if ($documentos->isEmpty())
                                    <div class="mi-perfil-empty-state">
                                        <span class="mi-perfil-empty-icon bg-primary-subtle text-primary">
                                            <i class="ri-folder-upload-line"></i>
                                        </span>
                                        <h5>Aun no tienes documentos</h5>
                                        <p>Sube tus documentos de verificacion para que administracion pueda revisar tu perfil.</p>
                                        @can('gestionar documentos proveedor')
                                            <button type="button" class="btn btn-primary btn-sm js-mi-documento-panel-toggle" data-panel-target="miDocumentoCreatePanel">
                                                <i class="ri-upload-2-line align-bottom me-1"></i>
                                                Subir documento
                                            </button>
                                        @endcan
                                    </div>
                                @endif

                                <div class="mi-documentos-empty-filter d-none" id="miDocumentosEmptyFilter">
                                    <div class="mi-perfil-empty-state">
                                        <span class="mi-perfil-empty-icon bg-info-subtle text-info">
                                            <i class="ri-folder-info-line"></i>
                                        </span>
                                        <h5>Carpeta vacia</h5>
                                        <p>No tienes documentos cargados en esta carpeta.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @can('gestionar documentos proveedor')
                @include('proveedor.documentos.partials.create')

                @foreach ($documentos as $documento)
                    @include('proveedor.documentos.partials.edit')
                @endforeach
            @endcan

            <div class="mi-documento-side-placeholder {{ $errors->any() ? 'd-none' : '' }}" id="miDocumentoSidePlaceholder">
                <span class="mi-documento-panel-icon bg-primary-subtle text-primary">
                    <i class="ri-folder-upload-line"></i>
                </span>
                <h5 class="mt-3 mb-1">Gestiona tus documentos</h5>
                <p class="text-muted mb-0">Usa el boton subir o el lapiz de un documento para editar aqui.</p>
            </div>
        </div>
    </div>

    <div class="alert mi-documento-tip mt-3 mb-0" role="alert">
        <div class="d-flex align-items-start gap-3">
            <span class="mi-documento-tip-icon">
                <i class="ri-lightbulb-line"></i>
            </span>
            <div>
                <span class="badge bg-primary-subtle text-primary mb-2">Consejo</span>
                <p class="mb-0">Manten tus documentos vigentes para que administracion pueda verificar tu perfil sin observaciones.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/miPerfilProveedorDocumentos.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedorDocumentos.js') }}"></script>
@endpush
