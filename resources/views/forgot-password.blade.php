@extends('layouts.guest')

@section('title', 'Recuperar contrasena')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Recuperar contraseña</h5>
    <p class="text-muted">Te enviaremos un enlace para restablecer tu contraseña</p>
</div>

<div class="p-2 mt-4">
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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
                value="{{ old('email') }}"
                placeholder="Ingresa tu correo"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                Enviar enlace de recuperacion
            </button>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<p class="mb-0">
    Ya recordaste tu contraseña?
    <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">
        Volver al login
    </a>
</p>
@endsection
