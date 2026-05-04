@extends('layouts.guest')

@section('title', 'Registro')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Crear nueva cuenta</h5>
    <p class="text-muted">Crea tu cuenta para continuar</p>
</div>

<div class="p-2 mt-4">
    <form class="needs-validation" novalidate method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">
                Correo electronico <span class="text-danger">*</span>
            </label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Ingresa tu correo"
                value="{{ old('email') }}"
                required
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor ingresa tu correo.</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">
                Nombre <span class="text-danger">*</span>
            </label>
            <input
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                name="name"
                placeholder="Ingresa tu nombre"
                value="{{ old('name') }}"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password-input">
                Contraseña <span class="text-danger">*</span>
            </label>
            <div class="position-relative auth-pass-inputgroup">
                <input
                    type="password"
                    class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                    id="password-input"
                    name="password"
                    placeholder="Ingresa tu contrasena"
                    required
                >
                <button
                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                    type="button"
                    id="password-addon"
                >
                    <i class="ri-eye-fill align-middle"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa tu contrasena.</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                Confirmar contrasena <span class="text-danger">*</span>
            </label>
            <input
                type="password"
                class="form-control"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Confirma tu contrasena"
                required
            >
            <div class="invalid-feedback">Por favor confirma tu contrasena.</div>
        </div>

        <div class="mb-4">
            <p class="mb-0 fs-12 text-muted fst-italic">
                Al registrarte aceptas los
                <a href="#" class="text-primary text-decoration-underline fst-normal fw-medium">terminos de uso</a>
            </p>
        </div>

        <div id="password-contain" class="p-3 bg-light mb-2 rounded">
            <h5 class="fs-13">La contrasena debe contener:</h5>
            <p class="fs-12 mb-2">Minimo <b>8 caracteres</b></p>
            <p class="fs-12 mb-2">Al menos una <b>letra minuscula</b></p>
            <p class="fs-12 mb-2">Al menos una <b>letra mayuscula</b></p>
            <p class="fs-12 mb-0">Al menos un <b>numero</b></p>
        </div>

        <div class="mt-4">
            <button class="btn btn-success w-100" type="submit">Registrarme</button>
        </div>

        <div class="mt-4 text-center">
            <div class="signin-other-title">
                <h5 class="fs-13 mb-4 title text-muted">Crear cuenta con</h5>
            </div>

            <div>
                <button type="button" class="btn btn-primary btn-icon waves-effect waves-light" disabled>
                    <i class="ri-facebook-fill fs-16"></i>
                </button>
                <button type="button" class="btn btn-danger btn-icon waves-effect waves-light" disabled>
                    <i class="ri-google-fill fs-16"></i>
                </button>
                <button type="button" class="btn btn-dark btn-icon waves-effect waves-light" disabled>
                    <i class="ri-github-fill fs-16"></i>
                </button>
                <button type="button" class="btn btn-info btn-icon waves-effect waves-light" disabled>
                    <i class="ri-twitter-fill fs-16"></i>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<p class="mb-0">
    Ya tienes una cuenta?
    <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">
        Inicia sesion
    </a>
</p>
@endsection
