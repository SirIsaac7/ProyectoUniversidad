@extends('layouts.guest')

@section('title', 'Restablecer contrasena')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Nueva contraseña</h5>
    <p class="text-muted">Ingresa tu nueva contraseña para continuar</p>
</div>

<div class="p-2 mt-4">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">
                Correo electronico <span class="text-danger">*</span>
            </label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
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

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">
                Confirmar contraseña <span class="text-danger">*</span>
            </label>
            <input
                type="password"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                id="password_confirmation"
                name="password_confirmation"
                required
            >
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                Restablecer contraseña
            </button>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<p class="mb-0">
    Volver a
    <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">
        Iniciar sesion
    </a>
</p>
@endsection
