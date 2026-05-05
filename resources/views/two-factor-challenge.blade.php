@extends('layouts.guest')

@section('title', 'Autenticacion en dos pasos')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Autenticacion en dos pasos</h5>
    <p class="text-muted">Ingresa el codigo de tu aplicacion autenticadora para continuar</p>
</div>

<div class="p-2 mt-4">
    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf

        <div class="mb-3">
            <label for="code" class="form-label">
                Codigo de autenticacion <span class="text-danger">*</span>
            </label>
            <input
                type="text"
                class="form-control @error('code') is-invalid @enderror"
                id="code"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="Ingresa el codigo de 6 digitos"
                required
                autofocus
            >
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                Verificar codigo
            </button>
        </div>
    </form>

    <div class="text-center my-4">
        <span class="text-muted">o usa un codigo de recuperacion</span>
    </div>

    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf

        <div class="mb-3">
            <label for="recovery_code" class="form-label">
                Codigo de recuperacion
            </label>
            <input
                type="text"
                class="form-control @error('recovery_code') is-invalid @enderror"
                id="recovery_code"
                name="recovery_code"
                autocomplete="one-time-code"
                placeholder="Ingresa un codigo de recuperacion"
            >
            @error('recovery_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-light w-100">
                Usar codigo de recuperacion
            </button>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<p class="mb-0 text-muted">
    Este segundo paso aplica tanto para acceso manual como con Google.
</p>
@endsection
