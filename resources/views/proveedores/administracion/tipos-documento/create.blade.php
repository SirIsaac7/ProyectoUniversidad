@extends('layouts.app')

@section('title', 'Crear tipo de documento')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Crear tipo de documento</h4>

            <div class="page-title-right">
                <a href="{{ route('tipos-documento-proveedor.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Formulario de tipo de documento</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('tipos-documento-proveedor.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-lg-6">
                    <label for="nombre" class="form-label">
                        Nombre <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa el nombre del tipo de documento.</div>
                    @enderror
                </div>

                <div class="col-lg-3">
                    <label for="obligatorio" class="form-label">
                        Obligatorio <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('obligatorio') is-invalid @enderror" id="obligatorio" name="obligatorio" required>
                        <option value="1" @selected(old('obligatorio') == '1')>Si</option>
                        <option value="0" @selected(old('obligatorio', '0') == '0')>No</option>
                    </select>
                    @error('obligatorio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-3">
                    <label for="estado" class="form-label">
                        Estado <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                        <option value="1" @selected(old('estado', '1') == '1')>Activo</option>
                        <option value="0" @selected(old('estado') == '0')>Inactivo</option>
                    </select>
                    @error('estado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripcion</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="text-end">
                        <a href="{{ route('tipos-documento-proveedor.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Guardar tipo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/tiposDocumentoProveedor.js') }}"></script>
@endpush
