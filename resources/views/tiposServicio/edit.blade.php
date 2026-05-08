@extends('layouts.app')

@section('title', 'Editar tipo de servicio')

@push('styles')
<link href="{{ asset('assets/libs/filepond/filepond.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/tiposServicio.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Editar tipo de servicio</h4>

            <div class="page-title-right">
                <a href="{{ route('tipos-servicio.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Actualizar tipo de servicio</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('tipos-servicio.update', $tipoServicio->id) }}" enctype="multipart/form-data">
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
                        value="{{ old('nombre', $tipoServicio->nombre) }}"
                        required
                    >
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa el nombre del tipo de servicio.</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label">Estado actual</label>
                    <div class="form-control bg-light-subtle">
                        {{ $tipoServicio->estado ? 'Activo' : 'Inactivo' }}
                    </div>
                </div>

                <div class="col-12">
                    <div class="tipo-servicio-rubros-box">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="rubros" class="form-label mb-0">
                                Rubros <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-info-subtle text-info">Seleccion multiple</span>
                        </div>
                        <p class="text-muted mb-3">
                            Ajusta los rubros relacionados con este tipo de servicio. Los cambios actualizaran inmediatamente la relacion actual.
                        </p>
                        <select
                            class="form-select js-rubros-select @error('rubros') is-invalid @enderror @error('rubros.*') is-invalid @enderror"
                            id="rubros"
                            name="rubros[]"
                            multiple
                            required
                        >
                            @foreach ($rubros as $rubro)
                                <option
                                    value="{{ $rubro->id }}"
                                    @selected(collect(old('rubros', $tipoServicio->rubros->pluck('id')->toArray()))->contains($rubro->id))
                                >
                                    {{ $rubro->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('rubros')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback d-block">Por favor selecciona al menos un rubro.</div>
                    @enderror
                    @error('rubros.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Puedes buscar y seleccionar varios rubros.</small>
                </div>

                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label for="imagen" class="form-label mb-0">Imagen</label>
                    </div>
                    <p class="text-muted mb-3">
                        Puedes reemplazar la imagen actual del tipo de servicio usando un archivo JPG, JPEG, PNG o WEBP de hasta 3MB.
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
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if ($tipoServicio->imagen)
                    <div class="col-12">
                        <div class="tipo-servicio-image-current card border mb-0">
                            <div class="card-body d-flex align-items-center gap-3">
                                <img
                                    src="{{ asset($tipoServicio->imagen) }}"
                                    alt="{{ $tipoServicio->nombre }}"
                                    class="rounded border tipo-servicio-image-current-preview"
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
                    >{{ old('descripcion', $tipoServicio->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="text-end">
                        <a href="{{ route('tipos-servicio.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Actualizar tipo de servicio
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
<script src="{{ asset('assets/js/tiposServicio.js') }}"></script>
@endpush
