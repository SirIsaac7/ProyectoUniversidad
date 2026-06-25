@extends('layouts.app')

@section('title', 'Mi perfil de proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mi perfil de proveedor</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

@php
    $fotoPortada = $perfilProveedor->foto_portada ? asset($perfilProveedor->foto_portada) : asset('assets/images/blog/overview.jpg');
    $estadoVerificacionClases = [
        'pendiente' => 'bg-warning-subtle text-warning',
        'aprobado' => 'bg-success-subtle text-success',
        'rechazado' => 'bg-danger-subtle text-danger',
    ];
    $estadoVerificacionIconos = [
        'pendiente' => 'ri-time-line',
        'aprobado' => 'ri-shield-check-line',
        'rechazado' => 'ri-error-warning-line',
    ];
@endphp

<div class="mi-perfil-dashboard">
    <div class="row g-3 align-items-stretch">
        <div class="col-xl-5">
            <div class="card mi-perfil-preview-card h-100">
                <div class="card-body">
                    <div class="mi-perfil-cover">
                        <img src="{{ $fotoPortada }}" alt="Foto de portada" class="js-mi-perfil-cover-preview">

                        @can('actualizar perfil proveedor')
                            <button type="button" class="mi-perfil-cover-action js-mi-perfil-edit-toggle" title="Editar perfil">
                                <i class="ri-pencil-line"></i>
                            </button>
                        @endcan
                    </div>

                    <div class="mi-perfil-avatar">
                        @if ($perfilProveedor->user?->avatar_url)
                            <img src="{{ $perfilProveedor->user->avatar_url }}" alt="{{ $perfilProveedor->user->name }}" class="mi-perfil-avatar-image" referrerpolicy="no-referrer">
                        @else
                            <span>{{ $perfilProveedor->user?->inicial ?? mb_strtoupper(mb_substr($perfilProveedor->nombre_publico, 0, 1)) }}</span>
                        @endif
                    </div>

                    <div class="text-center mt-4">
                        <h4 class="mb-1">{{ $perfilProveedor->nombre_publico }}</h4>
                        <p class="text-muted mb-3">{{ $perfilProveedor->user?->email }}</p>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <span class="badge {{ $estadoVerificacionClases[$perfilProveedor->estado_verificacion] ?? 'bg-secondary-subtle text-secondary' }}">
                                <i class="{{ $estadoVerificacionIconos[$perfilProveedor->estado_verificacion] ?? 'ri-information-line' }} align-bottom me-1"></i>
                                Verificacion: {{ ucfirst($perfilProveedor->estado_verificacion) }}
                            </span>

                            @if ($perfilProveedor->estado)
                                <span class="badge bg-success-subtle text-success">
                                    <i class="ri-checkbox-circle-line align-bottom me-1"></i>
                                    Activo
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="ri-close-circle-line align-bottom me-1"></i>
                                    Inactivo
                                </span>
                            @endif
                        </div>

                        <div class="mi-perfil-badge-row mt-3">
                            <span class="mi-perfil-soft-badge bg-primary-subtle text-primary">
                                <i class="ri-briefcase-line"></i>
                                {{ $perfilProveedor->anios_experiencia ?? 0 }} años de experiencia
                            </span>
                            <span class="mi-perfil-soft-badge {{ $perfilProveedor->foto_portada ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary' }}">
                                <i class="{{ $perfilProveedor->foto_portada ? 'ri-image-line' : 'ri-image-add-line' }}"></i>
                                {{ $perfilProveedor->foto_portada ? 'Portada configurada' : 'Sin portada propia' }}
                            </span>
                            <span class="mi-perfil-soft-badge {{ $perfilProveedor->descripcion ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                <i class="{{ $perfilProveedor->descripcion ? 'ri-file-check-line' : 'ri-file-warning-line' }}"></i>
                                {{ $perfilProveedor->descripcion ? 'Descripcion visible' : 'Descripcion pendiente' }}
                            </span>
                        </div>
                    </div>

                    <div class="mi-perfil-info-list mt-4">
                        <div class="mi-perfil-info-item">
                            <i class="ri-calendar-check-line"></i>
                            <div>
                                <small class="text-muted d-block">Años de experiencia</small>
                                <span>{{ $perfilProveedor->anios_experiencia ?? 0 }} años</span>
                            </div>
                        </div>

                        <div class="mi-perfil-info-item">
                            <i class="ri-file-text-line"></i>
                            <div>
                                <small class="text-muted d-block">Descripcion</small>
                                <span>{{ $perfilProveedor->descripcion ?: 'Aun no agregaste una descripcion publica.' }}</span>
                            </div>
                        </div>
                    </div>

                    @can('actualizar perfil proveedor')
                        <button type="button" class="btn btn-primary w-100 mt-4 js-mi-perfil-edit-toggle">
                            <i class="ri-pencil-line align-bottom me-1"></i>
                            Editar perfil
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card mi-perfil-detail-card h-100 {{ $errors->any() ? 'd-none' : '' }}" id="miPerfilResumenPanel">
                <div class="card-body">
                    <div class="mi-perfil-overview">
                        <div class="mi-perfil-overview-illustration" aria-hidden="true">
                            <svg viewBox="0 0 240 170" role="img" focusable="false">
                                <rect x="44" y="28" width="152" height="104" rx="16" class="mi-perfil-svg-window" />
                                <rect x="63" y="50" width="70" height="10" rx="5" class="mi-perfil-svg-line" />
                                <rect x="63" y="70" width="112" height="8" rx="4" class="mi-perfil-svg-muted" />
                                <rect x="63" y="88" width="92" height="8" rx="4" class="mi-perfil-svg-muted" />
                                <circle cx="170" cy="56" r="18" class="mi-perfil-svg-avatar" />
                                <path d="M158 110c8-14 25-14 34 0" class="mi-perfil-svg-stroke" />
                                <path d="M82 125l16 13 32-42" class="mi-perfil-svg-check" />
                            </svg>
                        </div>

                        <h5 class="mb-2">Tu perfil es tu carta de presentacion</h5>
                        <p class="text-muted mb-0">Mantener tus datos claros, una descripcion honesta y una portada profesional ayuda a que los clientes entiendan rapidamente que servicios ofreces y por que pueden confiar en ti.</p>
                    </div>

                    @if ($perfilProveedor->motivo_rechazo)
                        <div class="alert alert-warning mt-4 mb-0">
                            {{ $perfilProveedor->motivo_rechazo }}
                        </div>
                    @endif
                </div>
            </div>

            @include('proveedor.perfil.partials.edit')
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/miPerfilProveedorPerfil.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedorPerfil.js') }}"></script>
@endpush
