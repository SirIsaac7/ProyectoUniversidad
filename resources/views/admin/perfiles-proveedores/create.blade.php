@extends('layouts.app')

@section('title', 'Crear proveedor')

@push('styles')
<link href="{{ asset('assets/libs/filepond/filepond.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/perfilesProveedores.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Crear proveedor</h4>

            <div class="page-title-right">
                <a href="{{ route('perfiles-proveedores.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Formulario de proveedor</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('perfiles-proveedores.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="user_id" class="form-label">
                        Usuario con rol proveedor <span class="text-danger">*</span>
                    </label>
                    <select
                        class="form-select js-usuario-select @error('user_id') is-invalid @enderror"
                        id="user_id"
                        name="user_id"
                        required
                    >
                        <option value="">Selecciona un usuario proveedor</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" @selected(old('user_id') == $usuario->id)>
                                {{ $usuario->name }} - {{ $usuario->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback d-block js-usuario-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback js-usuario-feedback">Por favor selecciona un usuario con rol proveedor.</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label for="nombre_publico" class="form-label">
                        Nombre publico <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control @error('nombre_publico') is-invalid @enderror"
                        id="nombre_publico"
                        name="nombre_publico"
                        value="{{ old('nombre_publico') }}"
                        required
                    >
                    @error('nombre_publico')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa el nombre publico.</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="anios_experiencia" class="form-label">Años de experiencia</label>
                    <input
                        type="number"
                        class="form-control @error('anios_experiencia') is-invalid @enderror"
                        id="anios_experiencia"
                        name="anios_experiencia"
                        value="{{ old('anios_experiencia') }}"
                        min="0"
                        max="80"
                    >
                    @error('anios_experiencia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="estado_verificacion" class="form-label">
                        Estado de verificacion <span class="text-danger">*</span>
                    </label>
                    <select
                        class="form-select @error('estado_verificacion') is-invalid @enderror"
                        id="estado_verificacion"
                        name="estado_verificacion"
                        required
                    >
                        <option value="pendiente" @selected(old('estado_verificacion', 'pendiente') === 'pendiente')>Pendiente</option>
                        <option value="aprobado" @selected(old('estado_verificacion') === 'aprobado')>Aprobado</option>
                        <option value="rechazado" @selected(old('estado_verificacion') === 'rechazado')>Rechazado</option>
                    </select>
                    @error('estado_verificacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor selecciona el estado de verificacion.</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="estado" class="form-label">
                        Estado <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
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
                    <label for="foto_portada" class="form-label">Foto de portada</label>
                    <p class="text-muted mb-3">
                        Usa una imagen JPG, JPEG, PNG o WEBP de hasta 3MB.
                    </p>
                    <input
                        type="file"
                        class="filepond filepond-input-multiple @error('foto_portada') is-invalid @enderror"
                        id="foto_portada"
                        name="foto_portada"
                        accept=".jpg,.jpeg,.png,.webp"
                        data-max-file-size="3MB"
                        data-max-files="1"
                    >
                    @error('foto_portada')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripcion</label>
                    <textarea
                        class="form-control @error('descripcion') is-invalid @enderror"
                        id="descripcion"
                        name="descripcion"
                        rows="4"
                    >{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 js-motivo-rechazo-wrapper">
                    <label for="motivo_rechazo" class="form-label">Motivo de rechazo</label>
                    <textarea
                        class="form-control @error('motivo_rechazo') is-invalid @enderror"
                        id="motivo_rechazo"
                        name="motivo_rechazo"
                        rows="3"
                        @disabled(old('estado_verificacion', 'pendiente') !== 'rechazado')
                    >{{ old('motivo_rechazo') }}</textarea>
                    @error('motivo_rechazo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="text-end">
                        <a href="{{ route('perfiles-proveedores.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Guardar proveedor
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
<script src="{{ asset('assets/libs/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
<script src="{{ asset('assets/js/perfilesProveedores.js') }}"></script>
@endpush
