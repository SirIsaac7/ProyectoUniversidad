@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Editar usuario</h4>

            <div class="page-title-right">
                <a href="{{ route('usuarios.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actualizar usuario</h5>
            </div>

            <div class="card-body">
                <form class="needs-validation" novalidate method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name', $usuario->name) }}"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Por favor ingresa el nombre.</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">
                            Correo electronico <span class="text-danger">*</span>
                        </label>
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            value="{{ old('email', $usuario->email) }}"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Por favor ingresa un correo electronico valido.</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="celular" class="form-label">Celular</label>
                                <input
                                    type="text"
                                    class="form-control @error('celular') is-invalid @enderror"
                                    id="celular"
                                    name="celular"
                                    value="{{ old('celular', $usuario->celular) }}"
                                    placeholder="Ej: 67024115"
                                    inputmode="numeric"
                                    maxlength="8"
                                    pattern="[0-9]{8}"
                                >
                                @error('celular')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Debe tener exactamente 8 numeros. Si cambia, debera verificarse otra vez.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                <input
                                    type="date"
                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                    id="fecha_nacimiento"
                                    name="fecha_nacimiento"
                                    value="{{ old('fecha_nacimiento', optional($usuario->fecha_nacimiento)->format('Y-m-d')) }}"
                                    max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                >
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input
                            class="form-check-input @error('recibe_notificaciones_whatsapp') is-invalid @enderror"
                            type="checkbox"
                            role="switch"
                            id="recibe_notificaciones_whatsapp"
                            name="recibe_notificaciones_whatsapp"
                            value="1"
                            @checked(old('recibe_notificaciones_whatsapp', $usuario->recibe_notificaciones_whatsapp))
                        >
                        <label class="form-check-label" for="recibe_notificaciones_whatsapp">
                            Recibir notificaciones por WhatsApp
                        </label>
                        @error('recibe_notificaciones_whatsapp')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <div class="form-text">Solo se enviaran mensajes si el usuario tiene celular registrado.</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Actualizar usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/usuarios.js') }}"></script>
@endpush
