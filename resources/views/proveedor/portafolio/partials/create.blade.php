<div class="mi-portafolio-side-panel {{ $errors->any() && ! old('portafolio_id') ? '' : 'd-none' }}" id="miPortafolioCreatePanel">
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="mi-portafolio-panel-icon bg-primary-subtle text-primary">
            <i class="ri-add-line"></i>
        </span>
        <div>
            <h5 class="mb-1">Nuevo trabajo</h5>
            <small class="text-muted">Agrega una evidencia a tu portafolio.</small>
        </div>
    </div>

    <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.portafolio.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Titulo <span class="text-danger">*</span></label>
            <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" required>
            @error('titulo')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor ingresa el titulo.</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha del trabajo</label>
            <input type="date" name="fecha_trabajo" class="form-control @error('fecha_trabajo') is-invalid @enderror" value="{{ old('fecha_trabajo') }}">
            @error('fecha_trabajo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="4">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <label class="form-label mb-0">Imagenes <span class="text-danger">*</span></label>
                <span class="badge bg-info-subtle text-info">JPG, PNG, WEBP - max. 4 MB c/u</span>
            </div>
            <label class="mi-portafolio-upload-zone">
                <input type="file" name="imagenes[]" class="js-mi-portafolio-file-input @error('imagenes') is-invalid @enderror @error('imagenes.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp" multiple required>
                <span class="mi-portafolio-upload-icon">
                    <i class="ri-image-add-line"></i>
                </span>
                <strong>Selecciona hasta 4 imagenes</strong>
                <small class="text-muted">La primera imagen sera la portada del trabajo.</small>
                <span class="mi-portafolio-file-name">Ninguna imagen seleccionada</span>
            </label>
            <input type="hidden" name="imagenes_titulo[]" value="Evidencia">
            <input type="hidden" name="imagenes_descripcion[]" value="">
            @error('imagenes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('imagenes.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor selecciona al menos una imagen.</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line align-bottom me-1"></i>
                Guardar trabajo
            </button>
        </div>
    </form>
</div>
