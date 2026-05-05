@extends('layouts.guest')

@section('title', 'Confirmar contrasena')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Confirmar contraseña</h5>
    <p class="text-muted">Por seguridad, confirma tu contrasña para continuar</p>
</div>

<div class="p-2 mt-4">
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">
                Contraseña actual <span class="text-danger">*</span>
            </label>
            <input
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                name="password"
                placeholder="Ingresa tu contraseña"
                required
                autofocus
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                Confirmar
            </button>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<p class="mb-0 text-muted">
    Esta verificacion protege acciones sensibles como activar o desactivar 2FA.
</p>
@endsection
