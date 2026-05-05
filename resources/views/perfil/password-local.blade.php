@extends('layouts.app')

@section('title', 'Contraseña local')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Contraseña local</h4>

            <div class="page-title-right">
                <a href="{{ route('perfil.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

@if (session('status') === 'local-password-required')
    <div class="alert alert-info">
        Como ingresaste con Google, ahora debes definir una contraseña local para poder confirmar acciones sensibles, cambiar tu contraseña y activar la verificacion en dos pasos.
    </div>
@endif

<div class="row">
    <div class="col-xxl-8">
        <div class="card border">
            <div class="card-header">
                <h5 class="card-title mb-0">Definir contraseña local</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('perfil.password-local.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label for="password" class="form-label">
                                Nueva contraseña <span class="text-danger">*</span>
                            </label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                required
                            >
                            @error('password')
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
                                    Guardar contraseña local
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
                    alt="Contrasena local"
                    class="img-fluid perfil-side-visual mb-4"
                >

                <h5 class="mb-2">Activa tu acceso local</h5>
                <p class="text-muted mb-0">
                    Esta contraseña pertenece a tu cuenta en este sistema. No reemplaza tu contraseña de Google.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
