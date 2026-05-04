@extends('layouts.guest')

@section('title', 'Iniciar sesion')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Bienvenido</h5>
    <p class="text-muted">Inicia sesion para continuar</p>
</div>

<div class="p-2 mt-4">
    <form class="needs-validation" novalidate method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electronico</label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Ingresa tu correo"
                value="{{ old('email') }}"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor ingresa tu correo.</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="float-end">
                <a href="{{ route('password.request') }}" class="text-muted">Olvidaste tu contraseña?</a>
            </div>

            <label class="form-label" for="password-input">Contraseña</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input
                    type="password"
                    class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                    id="password-input"
                    name="password"
                    placeholder="Ingresa tu contraseña"
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

        @if ($errors->has('email'))
            <div class="alert alert-danger py-2">
                {{ $errors->first('email') }}
            </div>
        @endif

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="auth-remember-check">
            <label class="form-check-label" for="auth-remember-check">Recordarme</label>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary w-100" type="submit">Iniciar sesion</button>
        </div>

        <div class="mt-4 text-center">
            <div class="signin-other-title">
                <h5 class="fs-13 mb-4 title">O ingresa con</h5>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-icon waves-effect waves-light" disabled>
                    <i class="ri-facebook-fill fs-16"></i>
                </button>
                <a href="{{ route('google.redirect') }}" class="btn btn-danger btn-icon waves-effect waves-light">
                    <i class="ri-google-fill fs-16"></i>
                </a>
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
    No tienes una cuenta?
    <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-underline">
        Registrate
    </a>
</p>
@endsection
