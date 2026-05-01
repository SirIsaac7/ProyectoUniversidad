@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Bienvenido</h5>
    <p class="text-muted">Inicia sesión para continuar</p>
</div>

<div class="p-2 mt-4">
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electronico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Ingresa tu correo" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contrasena</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 password-input" id="password" name="password" placeholder="Ingresa tu contrasena" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon">
                    <i class="ri-eye-fill align-middle"></i>
                </button>
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Recordarme</label>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary w-100" type="submit">Iniciar sesion</button>
        </div>
    </form>
</div>

@endsection
