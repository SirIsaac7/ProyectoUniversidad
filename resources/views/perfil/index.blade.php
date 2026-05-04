@extends('layouts.app')

@section('title', 'Mi perfil')

@push('styles')
<link href="{{ asset('assets/css/perfil.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
@php
    $usuario = auth()->user();
    $rolActual = $usuario->roles->first()?->name ?? 'Sin rol';
    $tipoAcceso = $usuario->google_id ? 'Google' : 'Manual';
    $estadoCorreo = $usuario->hasVerifiedEmail() ? 'Verificado' : 'Pendiente';
@endphp

@if (session('status') === 'profile-information-updated')
    <div class="d-none" id="perfil-success-message" data-message="La informacion del perfil fue actualizada correctamente."></div>
@endif

@if (session('status') === 'password-updated')
    <div class="d-none" id="perfil-success-message" data-message="La contraseña fue actualizada correctamente."></div>
@endif

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mi perfil</h4>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="perfil-cover">
        <div class="perfil-header-content">
            <div class="d-flex align-items-end perfil-avatar-wrap">
                <div class="flex-shrink-0 me-3">
                    @if ($usuario->avatar)
                        <img
                            src="{{ $usuario->avatar }}"
                            class="rounded-circle avatar-xxl img-thumbnail perfil-avatar-image"
                            alt="Avatar"
                        >
                    @else
                        <div class="avatar-xxl">
                            <div class="avatar-title rounded-circle perfil-avatar-ring fs-1 img-thumbnail border-0">
                                {{ strtoupper(substr($usuario->name, 0, 1)) }}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="flex-grow-1 pb-2 text-white">
                    <h3 class="mb-1 text-white">{{ $usuario->name }}</h3>
                    <p class="mb-0 text-white text-opacity-75">{{ $usuario->email }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="perfil-meta-card perfil-meta-card--role">
                    <div class="perfil-meta-label">Rol actual</div>
                    <div class="fw-semibold fs-15">{{ $rolActual }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="perfil-meta-card perfil-meta-card--access">
                    <div class="perfil-meta-label">Tipo de acceso</div>
                    <div class="fw-semibold fs-15">{{ $tipoAcceso }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="perfil-meta-card perfil-meta-card--account">
                    <div class="perfil-meta-label">Estado de cuenta</div>
                    <div class="fw-semibold fs-15">{{ $usuario->estado ? 'Activo' : 'Inactivo' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="perfil-meta-card perfil-meta-card--email">
                    <div class="perfil-meta-label">Estado del correo</div>
                    <div class="fw-semibold fs-15">{{ $estadoCorreo }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="perfil-meta-card perfil-meta-card--dates">
                    <div class="perfil-meta-label">Fecha de registro</div>
                    <div class="fw-semibold fs-15">{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="perfil-meta-card perfil-meta-card--dates">
                    <div class="perfil-meta-label">Ultima actualizacion</div>
                    <div class="fw-semibold fs-15">{{ optional($usuario->updated_at)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0 perfil-tab-nav" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#perfil-presentacion" role="tab">
                    Presentacion
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#perfil-ajustes" role="tab">
                    Ajustes
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content text-muted">
            <div class="tab-pane active" id="perfil-presentacion" role="tabpanel">
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Resumen de cuenta</h5>
                                <p class="text-muted">
                                    Este espacio resume tu informacion principal dentro del sistema. Desde aqui puedes identificar
                                    tu rol, el tipo de acceso con el que ingresaste y el estado actual de tu cuenta.
                                </p>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="text-muted mb-1">Nombre completo</p>
                                            <h6 class="mb-0">{{ $usuario->name }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="text-muted mb-1">Correo electronico</p>
                                            <h6 class="mb-0">{{ $usuario->email }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="text-muted mb-1">Metodo de autenticacion</p>
                                            <h6 class="mb-0">{{ $tipoAcceso }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="text-muted mb-1">Rol asignado</p>
                                            <h6 class="mb-0">{{ $rolActual }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Estado de seguridad</h5>

                                <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                                    <div>
                                        <h6 class="mb-1">Correo electronico</h6>
                                        <p class="text-muted mb-0">Estado de verificacion de tu cuenta.</p>
                                    </div>
                                    @if ($usuario->hasVerifiedEmail())
                                        <span class="badge bg-success-subtle text-success">Verificado</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                                    @endif
                                </div>

                                @if (! $usuario->hasVerifiedEmail())
                                    <form method="POST" action="{{ route('verification.send') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100">
                                            Reenviar correo de verificacion
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="perfil-ajustes" role="tabpanel">
                <div class="row">
                    <div class="col-xxl-8">
                        <div class="card border">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informacion del perfil</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('user-profile-information.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <label for="name" class="form-label">
                                                Nombre <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control @error('name', 'updateProfileInformation') is-invalid @enderror"
                                                id="name"
                                                name="name"
                                                value="{{ old('name', $usuario->name) }}"
                                                required
                                            >
                                            @error('name', 'updateProfileInformation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="email" class="form-label">
                                                Correo electronico <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="email"
                                                class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror"
                                                id="email"
                                                name="email"
                                                value="{{ old('email', $usuario->email) }}"
                                                required
                                            >
                                            @error('email', 'updateProfileInformation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ri-save-line align-bottom me-1"></i>
                                                    Guardar cambios
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Cambiar contraseña</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('user-password.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-3">
                                        <div class="col-lg-12">
                                            <label for="current_password" class="form-label">
                                                Contraseña actual <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="password"
                                                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                                id="current_password"
                                                name="current_password"
                                                required
                                            >
                                            @error('current_password', 'updatePassword')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="password" class="form-label">
                                                Nueva contraseña <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="password"
                                                class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                                id="password"
                                                name="password"
                                                required
                                            >
                                            @error('password', 'updatePassword')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="password_confirmation" class="form-label">
                                                Confirmar contraseña <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                required
                                            >
                                        </div>

                                        <div class="col-12">
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ri-lock-password-line align-bottom me-1"></i>
                                                    Actualizar contraseña
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-4">
                        <div class="card border perfil-side-visual-card">
                            <div class="card-body text-center p-4">
                                <img
                                    src="{{ asset('assets/images/user-illustarator-2.png') }}"
                                    alt="Perfil"
                                    class="img-fluid perfil-side-visual mb-4"
                                >

                                <h5 class="mb-2">Tu cuenta, bajo control</h5>
                                <p class="text-muted mb-4">
                                    Desde esta seccion puedes mantener actualizada tu informacion personal y reforzar la seguridad de acceso a tu cuenta.
                                </p>

                                <div class="text-start">
                                    <div class="border rounded p-3 mb-3 bg-light-subtle">
                                        <p class="text-muted mb-1">Acceso actual</p>
                                        <h6 class="mb-0">{{ $tipoAcceso }}</h6>
                                    </div>

                                    <div class="border rounded p-3 mb-3 bg-light-subtle">
                                        <p class="text-muted mb-1">Rol asignado</p>
                                        <h6 class="mb-0">{{ $rolActual }}</h6>
                                    </div>

                                    <div class="border rounded p-3 bg-light-subtle">
                                        <p class="text-muted mb-1">Correo</p>
                                        <h6 class="mb-0">{{ $estadoCorreo }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/perfil.js') }}"></script>
@endpush
