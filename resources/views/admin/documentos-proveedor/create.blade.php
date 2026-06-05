@extends('layouts.app')

@section('title', 'Crear documento del proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Crear documento del proveedor</h4>

            <div class="page-title-right">
                <a href="{{ route('documentos-proveedor.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Formulario de documento</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('documentos-proveedor.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-lg-6">
                    <label for="perfil_proveedor_id" class="form-label">
                        Proveedor <span class="text-danger">*</span>
                    </label>
                    <select name="perfil_proveedor_id" id="perfil_proveedor_id" class="form-select js-perfil-proveedor-select @error('perfil_proveedor_id') is-invalid @enderror" required>
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

                <div class="col-lg-6">
                    <label for="tipo_documento_proveedor_id" class="form-label">
                        Tipo de documento <span class="text-danger">*</span>
                    </label>
                    <select name="tipo_documento_proveedor_id" id="tipo_documento_proveedor_id" class="form-select js-tipo-documento-select @error('tipo_documento_proveedor_id') is-invalid @enderror" required>
                        <option value="">Selecciona un tipo de documento</option>
                        @foreach ($tiposDocumentoProveedor as $tipoDocumentoProveedor)
                            <option value="{{ $tipoDocumentoProveedor->id }}" @selected(old('tipo_documento_proveedor_id') == $tipoDocumentoProveedor->id)>
                                {{ $tipoDocumentoProveedor->nombre }} {{ $tipoDocumentoProveedor->obligatorio ? '(Obligatorio)' : '(Opcional)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_documento_proveedor_id')
                        <div class="invalid-feedback d-block js-tipo-documento-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback js-tipo-documento-feedback">Por favor selecciona el tipo de documento.</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label for="archivo" class="form-label">
                        Archivo <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control @error('archivo') is-invalid @enderror" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                    <div class="form-text">Formatos permitidos: PDF, JPG, PNG o WEBP. Maximo 5 MB.</div>
                    @error('archivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor selecciona un archivo.</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label for="estado_revision" class="form-label">
                        Estado de revision <span class="text-danger">*</span>
                    </label>
                    <select name="estado_revision" id="estado_revision" class="form-select js-estado-revision @error('estado_revision') is-invalid @enderror" required>
                        <option value="pendiente" @selected(old('estado_revision', 'pendiente') === 'pendiente')>Pendiente</option>
                        <option value="aprobado" @selected(old('estado_revision') === 'aprobado')>Aprobado</option>
                        <option value="rechazado" @selected(old('estado_revision') === 'rechazado')>Rechazado</option>
                    </select>
                    <div class="form-text js-estado-revision-help"></div>
                    @error('estado_revision')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label for="estado" class="form-label">
                        Estado <span class="text-danger">*</span>
                    </label>
                    <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
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
                    <label for="observacion" class="form-label">Observacion</label>
                    <textarea class="form-control js-observacion-revision @error('observacion') is-invalid @enderror" id="observacion" name="observacion" rows="4">{{ old('observacion') }}</textarea>
                    <div class="form-text">Obligatoria si el documento esta rechazado.</div>
                    @error('observacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="text-end">
                        <a href="{{ route('documentos-proveedor.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Guardar documento
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
<script src="{{ asset('assets/js/documentosProveedor.js') }}"></script>
@endpush
