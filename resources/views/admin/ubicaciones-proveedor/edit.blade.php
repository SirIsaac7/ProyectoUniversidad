@extends('layouts.app')

@section('title', 'Editar ubicacion')

@push('styles')
<link href="{{ asset('assets/css/ubicacionesProveedor.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Editar ubicacion</h4>

            <div class="page-title-right">
                <a href="{{ route('ubicaciones-proveedor.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actualizar ubicacion</h5>
            </div>

            <div class="card-body">
                <form class="needs-validation" novalidate method="POST" action="{{ route('ubicaciones-proveedor.update', $ubicacionProveedor->id) }}">
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
                                    <option value="{{ $perfilProveedor->id }}" @selected(old('perfil_proveedor_id', $ubicacionProveedor->perfil_proveedor_id) == $perfilProveedor->id)>
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
                            <label for="zona" class="form-label">Zona</label>
                            <input type="text" name="zona" id="zona" class="form-control @error('zona') is-invalid @enderror" value="{{ old('zona', $ubicacionProveedor->zona) }}">
                            @error('zona')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="direccion" class="form-label">Direccion</label>
                            <input type="text" name="direccion" id="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $ubicacionProveedor->direccion) }}">
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label">Seleccionar punto en el mapa</label>
                            <div
                                id="ubicacionProveedorMap"
                                class="ubicacion-proveedor-map"
                                data-lat="{{ old('latitud', $ubicacionProveedor->latitud) }}"
                                data-lng="{{ old('longitud', $ubicacionProveedor->longitud) }}"
                                data-radio="{{ old('radio_cobertura_km', $ubicacionProveedor->radio_cobertura_km ?? 1) }}"
                                data-use-current-location="false"
                            ></div>
                            <small class="text-muted">Haz clic en el mapa para ajustar el punto del proveedor.</small>
                        </div>

                        <div class="col-lg-4">
                            <div class="row g-3">
                                <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud', $ubicacionProveedor->latitud) }}">
                                <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud', $ubicacionProveedor->longitud) }}">

                                <div class="col-12">
                                    <div class="border rounded p-3 bg-light-subtle">
                                        <span class="d-block text-muted mb-1">Punto seleccionado</span>
                                        <span class="fw-semibold js-coordenadas-seleccionadas">Ubicacion actual</span>
                                    </div>
                                    @error('latitud')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('longitud')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="radio_cobertura_km" class="form-label">Radio de cobertura</label>
                                    <input type="hidden" name="radio_cobertura_km" id="radio_cobertura_km" value="{{ old('radio_cobertura_km', min($ubicacionProveedor->radio_cobertura_km ?? 1, 5)) }}">
                                    <input type="range" min="10" max="50" step="1" id="radio_cobertura_slider" class="form-range @error('radio_cobertura_km') is-invalid @enderror" value="{{ old('radio_cobertura_km', min($ubicacionProveedor->radio_cobertura_km ?? 1, 5)) * 10 }}">
                                    <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                                        <span>1 km</span>
                                        <span class="fw-semibold text-body">
                                            <span id="radioCoberturaValue">{{ old('radio_cobertura_km', min($ubicacionProveedor->radio_cobertura_km ?? 1, 5)) }}</span> km
                                        </span>
                                        <span>5 km</span>
                                    </div>
                                    @error('radio_cobertura_km')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted js-radio-help"></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('ubicaciones-proveedor.index') }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    Actualizar ubicacion
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
<script src="{{ asset('assets/js/ubicacionesProveedor.js') }}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initUbicacionesProveedorMaps"></script>
@endpush
