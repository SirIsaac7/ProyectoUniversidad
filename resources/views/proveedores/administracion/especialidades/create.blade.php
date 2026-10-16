@extends('layouts.app')

@section('title', 'Asignar especialidad')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Asignar especialidad</h4>

            <div class="page-title-right">
                <a href="{{ route('proveedor-especialidades.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Formulario de especialidad del proveedor</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('proveedor-especialidades.store') }}">
            @csrf

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
                                    <option value="{{ $perfilProveedor->id }}" @selected(old('perfil_proveedor_id') == $perfilProveedor->id)>
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
                            <label for="especialidad_id" class="form-label">
                                Especialidad <span class="text-danger">*</span>
                            </label>
                            <select
                                name="especialidad_id"
                                id="especialidad_id"
                                class="form-select js-especialidad-select @error('especialidad_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Selecciona una especialidad</option>
                                @foreach ($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}" @selected(old('especialidad_id') == $especialidad->id)>
                                        {{ $especialidad->rubroTipoServicio?->rubro?->nombre }} - {{ $especialidad->rubroTipoServicio?->tipoServicio?->nombre }} - {{ $especialidad->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('especialidad_id')
                                <div class="invalid-feedback d-block js-especialidad-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback js-especialidad-feedback">Por favor selecciona una especialidad.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="es_principal" class="form-label">
                                Principal <span class="text-danger">*</span>
                            </label>
                            <select
                                name="es_principal"
                                id="es_principal"
                                class="form-select @error('es_principal') is-invalid @enderror"
                                required
                            >
                                <option value="0" @selected(old('es_principal', '0') === '0')>No</option>
                                <option value="1" @selected(old('es_principal') === '1')>Si</option>
                            </select>
                            @error('es_principal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona si es principal.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="estado" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select
                                name="estado"
                                id="estado"
                                class="form-select @error('estado') is-invalid @enderror"
                                required
                            >
                                <option value="1" @selected(old('estado', '1') === '1')>Activo</option>
                                <option value="0" @selected(old('estado') === '0')>Inactivo</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona el estado.</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('proveedor-especialidades.index') }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    Guardar asignacion
                                </button>
                            </div>
                        </div>
                    </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/proveedorEspecialidades.js') }}"></script>
@endpush
