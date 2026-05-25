<h5 class="mb-3">Mi ubicacion</h5>

@can('gestionar ubicacion proveedor')
    <form class="needs-validation border rounded p-3 mb-3" novalidate method="POST" action="{{ route('mi-perfil-proveedor.ubicacion.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="zona" class="form-label">Zona</label>
                <input type="text" name="zona" id="zona" class="form-control @error('zona') is-invalid @enderror" value="{{ old('zona', $perfilProveedor->ubicacion?->zona) }}">
                @error('zona')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="radio_cobertura_km" class="form-label">Radio de cobertura</label>
                <input type="range" min="1" max="5" step="1" class="form-range js-mi-radio-slider" value="{{ old('radio_cobertura_km', $perfilProveedor->ubicacion?->radio_cobertura_km ?? 1) }}">
                <input type="hidden" name="radio_cobertura_km" id="radio_cobertura_km" value="{{ old('radio_cobertura_km', $perfilProveedor->ubicacion?->radio_cobertura_km ?? 1) }}">
                <div class="form-text">Cobertura: <span id="miRadioCoberturaValue">{{ old('radio_cobertura_km', $perfilProveedor->ubicacion?->radio_cobertura_km ?? 1) }}</span> km</div>
                @error('radio_cobertura_km') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="direccion" class="form-label">Direccion</label>
                <input type="text" name="direccion" id="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $perfilProveedor->ubicacion?->direccion) }}">
                @error('direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="latitud" class="form-label">Latitud <span class="text-danger">*</span></label>
                <input type="text" name="latitud" id="latitud" class="form-control @error('latitud') is-invalid @enderror" value="{{ old('latitud', $perfilProveedor->ubicacion?->latitud) }}" required>
                @error('latitud')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa la latitud.</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="longitud" class="form-label">Longitud <span class="text-danger">*</span></label>
                <input type="text" name="longitud" id="longitud" class="form-control @error('longitud') is-invalid @enderror" value="{{ old('longitud', $perfilProveedor->ubicacion?->longitud) }}" required>
                @error('longitud')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa la longitud.</div>
                @enderror
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light js-mi-ubicacion-actual">
                    <i class="ri-focus-3-line align-bottom me-1"></i>
                    Usar mi ubicacion
                </button>
                <button type="submit" class="btn btn-primary">Guardar ubicacion</button>
            </div>
        </div>
    </form>
@endcan

@if ($perfilProveedor->ubicacion)
    <div class="border rounded p-3">
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Zona</small>
                <div class="fw-semibold">{{ $perfilProveedor->ubicacion->zona ?: 'Sin zona' }}</div>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Radio de cobertura</small>
                <div class="fw-semibold">{{ $perfilProveedor->ubicacion->radio_cobertura_km ?: 1 }} km</div>
            </div>
            <div class="col-12">
                <small class="text-muted d-block">Direccion</small>
                <div>{{ $perfilProveedor->ubicacion->direccion ?: 'Sin direccion' }}</div>
            </div>
        </div>
    </div>
@else
    <div class="text-center text-muted py-4">Aun no tienes ubicacion registrada.</div>
@endif
