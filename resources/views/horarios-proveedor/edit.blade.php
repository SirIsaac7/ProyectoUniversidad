@extends('layouts.app')

@section('title', 'Editar horario')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col">
                <h4 class="mb-sm-0">Editar horario</h4>
            </div>

            <div class="col-auto">
                <a href="{{ route('horarios-proveedor.index') }}" class="btn btn-light">
                    Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actualizar horario</h5>
            </div>

            <div class="card-body">
                <form class="needs-validation" novalidate method="POST" action="{{ route('horarios-proveedor.update', $horarioProveedor->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="perfil_proveedor_id" class="form-label">
                                Proveedor <span class="text-danger">*</span>
                            </label>
                            <select
                                name="perfil_proveedor_id"
                                id="perfil_proveedor_id"
                                class="form-select js-perfil-proveedor-select @error('perfil_proveedor_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Selecciona un proveedor</option>
                                @foreach ($perfilesProveedores as $perfilProveedor)
                                    <option value="{{ $perfilProveedor->id }}" @selected(old('perfil_proveedor_id', $horarioProveedor->perfil_proveedor_id) == $perfilProveedor->id)>
                                        {{ $perfilProveedor->nombre_publico }} - {{ $perfilProveedor->user?->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('perfil_proveedor_id')
                                <div class="invalid-feedback d-block js-perfil-proveedor-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback js-perfil-proveedor-feedback">Por favor selecciona un proveedor.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="dia_semana" class="form-label">
                                Dia <span class="text-danger">*</span>
                            </label>
                            <select name="dia_semana" id="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                                <option value="">Selecciona un dia</option>
                                @foreach ([1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'] as $numero => $dia)
                                    <option value="{{ $numero }}" @selected(old('dia_semana', $horarioProveedor->dia_semana) == $numero)>{{ $dia }}</option>
                                @endforeach
                            </select>
                            @error('dia_semana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona el dia.</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="hora_inicio" class="form-label">
                                Hora inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror" value="{{ old('hora_inicio', optional($horarioProveedor->hora_inicio)->format('H:i')) }}" required>
                            @error('hora_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa la hora de inicio.</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="hora_fin" class="form-label">
                                Hora fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror" value="{{ old('hora_fin', optional($horarioProveedor->hora_fin)->format('H:i')) }}" required>
                            @error('hora_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa la hora de fin.</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="tipo_atencion" class="form-label">
                                Tipo de atencion <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_atencion" id="tipo_atencion" class="form-select @error('tipo_atencion') is-invalid @enderror" required>
                                <option value="mixto" @selected(old('tipo_atencion', $horarioProveedor->tipo_atencion) === 'mixto')>Mixto</option>
                                <option value="domicilio" @selected(old('tipo_atencion', $horarioProveedor->tipo_atencion) === 'domicilio')>Domicilio</option>
                                <option value="local" @selected(old('tipo_atencion', $horarioProveedor->tipo_atencion) === 'local')>Local</option>
                                <option value="remoto" @selected(old('tipo_atencion', $horarioProveedor->tipo_atencion) === 'remoto')>Remoto</option>
                            </select>
                            @error('tipo_atencion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                            @enderror
                            <small class="text-muted js-tipo-atencion-help"></small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Disponibilidad actual</label>
                            <div>
                                @if ($horarioProveedor->disponible)
                                    <span class="badge bg-success-subtle text-success">Disponible</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">No disponible</span>
                                @endif
                            </div>
                            <small class="text-muted js-disponible-help" data-fixed="{{ $horarioProveedor->disponible ? '1' : '0' }}"></small>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('horarios-proveedor.index') }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    Actualizar horario
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/horariosProveedor.js') }}"></script>
@endpush
