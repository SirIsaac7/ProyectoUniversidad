@extends('layouts.app')

@section('title', 'Editar rubro')

@push('styles')
<link href="{{ asset('assets/libs/filepond/filepond.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/rubros.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Editar rubro</h4>

            <div class="page-title-right">
                <a href="{{ route('rubros.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Actualizar rubro</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('rubros.update', $rubro->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-6">
                    <label for="nombre" class="form-label">
                        Nombre <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control @error('nombre') is-invalid @enderror"
                        id="nombre"
                        name="nombre"
                        value="{{ old('nombre', $rubro->nombre) }}"
                        required
                    >
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa el nombre del rubro.</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label">Estado actual</label>
                    <div class="form-control bg-light-subtle">
                        {{ $rubro->estado ? 'Activo' : 'Inactivo' }}
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label for="imagen" class="form-label mb-0">Imagen</label>
                    </div>
                    <p class="text-muted mb-3">
                        Puedes reemplazar la imagen actual del rubro usando un archivo JPG, JPEG, PNG o WEBP de hasta 3MB.
                    </p>
                    <input
                        type="file"
                        class="filepond filepond-input-multiple @error('imagen') is-invalid @enderror"
                        id="imagen"
                        name="imagen"
                        accept=".jpg,.jpeg,.png,.webp"
                        data-max-file-size="3MB"
                        data-max-files="1"
                    >
                    @error('imagen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($rubro->imagen)
                    <div class="col-12">
                        <div class="rubro-image-current card border mb-0">
                            <div class="card-body d-flex align-items-center gap-3">
                                <img
                                    src="{{ asset($rubro->imagen) }}"
                                    alt="{{ $rubro->nombre }}"
                                    class="rounded border rubro-image-current-preview"
                                >
                                <div>
                                    <h6 class="mb-1">Imagen actual</h6>
                                    <p class="text-muted mb-0">
                                        Si seleccionas una nueva imagen, esta vista sera reemplazada al guardar los cambios.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripcion</label>
                    <textarea
                        class="form-control @error('descripcion') is-invalid @enderror"
                        id="descripcion"
                        name="descripcion"
                        rows="4"
                    >{{ old('descripcion', $rubro->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="text-end">
                        <a href="{{ route('rubros.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Actualizar rubro
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js') }}"></script>
<script src="{{ asset('assets/js/rubros.js') }}"></script>
@endpush
