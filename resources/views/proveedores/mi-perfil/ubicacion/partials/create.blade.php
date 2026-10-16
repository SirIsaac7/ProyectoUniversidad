<div class="mi-ubicacion-form-panel {{ $errors->any() ? '' : 'd-none' }}" id="miUbicacionFormPanel">
    <div class="mb-3">
        <h5 class="mb-1">Agregar ubicación</h5>
        <small class="text-muted">Define tu punto principal de atención o cobertura.</small>
    </div>

    <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.ubicacion.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <label for="zona" class="form-label">Zona</label>
                <input
                    type="text"
                    name="zona"
                    id="zona"
                    class="form-control @error('zona') is-invalid @enderror"
                    value="{{ old('zona') }}"
                    placeholder="Ejemplo: Sopocachi"
                >
                @error('zona')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="direccion" class="form-label">Direccion</label>
                <input
                    type="text"
                    name="direccion"
                    id="direccion"
                    class="form-control @error('direccion') is-invalid @enderror"
                    value="{{ old('direccion') }}"
                    placeholder="Ejemplo: Av. 6 de Agosto"
                >
                @error('direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="radio_cobertura_km" class="form-label">Radio de cobertura</label>
                <input type="hidden" name="radio_cobertura_km" id="radio_cobertura_km" value="{{ old('radio_cobertura_km', $radioCobertura) }}">
                <input
                    type="range"
                    min="10"
                    max="50"
                    step="1"
                    id="radio_cobertura_slider"
                    class="form-range @error('radio_cobertura_km') is-invalid @enderror"
                    value="{{ old('radio_cobertura_km', $radioCobertura) * 10 }}"
                >
                <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                    <span>1 km</span>
                    <span class="fw-semibold text-body">
                        <span id="radioCoberturaValue">{{ old('radio_cobertura_km', $radioCobertura) }}</span> km
                    </span>
                    <span>5 km</span>
                </div>
                @error('radio_cobertura_km')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted js-radio-help"></small>
            </div>

            <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud', '-16.5000000') }}">
            <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud', '-68.1500000') }}">

            <div class="col-12">
                <div class="border rounded p-3 bg-light-subtle">
                    <span class="d-block text-muted mb-1">Punto seleccionado</span>
                    <span class="fw-semibold js-coordenadas-seleccionadas">Mapa de La Paz</span>
                </div>
                @error('latitud')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('longitud')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <button type="button" class="btn btn-light js-mi-ubicacion-form-toggle">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-bottom me-1"></i>
                        Guardar ubicacion
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
