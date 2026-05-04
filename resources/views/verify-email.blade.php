@extends('layouts.guest')

@section('title', 'Verificar correo')

@section('content')
<div class="text-center mt-2">
    <h5 class="text-primary">Verifica tu correo</h5>
    <p class="text-muted">Antes de continuar, revisa tu bandeja de entrada.</p>
</div>

<div class="p-2 mt-4">
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success" role="alert">
            Se envio un nuevo enlace de verificacion a tu correo electronico.
        </div>
    @endif

    <div class="alert alert-info" role="alert">
        Gracias por registrarte. Antes de empezar, confirma tu direccion de correo haciendo clic en el enlace que te enviamos.
        Si no lo recibiste, puedes solicitar otro desde aqui.
    </div>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                Reenviar correo de verificacion
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-light w-100">
            Cerrar sesion
        </button>
    </form>
</div>
@endsection

@section('auth-links')
<p class="mb-0 text-muted">
    Debes verificar tu correo para continuar.
</p>
@endsection
