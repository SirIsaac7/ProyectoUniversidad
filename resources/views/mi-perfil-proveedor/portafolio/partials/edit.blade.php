<div class="mi-portafolio-side-panel {{ $errors->any() && (int) old('portafolio_id') === $trabajo->id ? '' : 'd-none' }}" id="miPortafolioEditPanel{{ $trabajo->id }}">
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="mi-portafolio-panel-icon bg-warning-subtle text-warning">
            <i class="ri-pencil-line"></i>
        </span>
        <div>
            <h5 class="mb-1">Editar trabajo</h5>
            <small class="text-muted">{{ $trabajo->titulo }}</small>
        </div>
    </div>

    <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.portafolio.update', $trabajo->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="portafolio_id" value="{{ $trabajo->id }}">

        <div class="mb-3">
            <label class="form-label">Titulo <span class="text-danger">*</span></label>
            <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $trabajo->titulo) }}" required>
            @error('titulo')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor ingresa el titulo.</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha del trabajo</label>
            <input type="date" name="fecha_trabajo" class="form-control @error('fecha_trabajo') is-invalid @enderror" value="{{ old('fecha_trabajo', optional($trabajo->fecha_trabajo)->format('Y-m-d')) }}">
            @error('fecha_trabajo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="4">{{ old('descripcion', $trabajo->descripcion) }}</textarea>
            @error('descripcion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($trabajo->imagenes->isNotEmpty())
            <div class="mb-3">
                <label class="form-label">Imagenes actuales</label>
                <div class="mi-portafolio-current-images">
                    @foreach ($trabajo->imagenes as $imagen)
                        <div class="mi-portafolio-current-image">
                            <img src="{{ asset($imagen->imagen) }}" alt="{{ $imagen->titulo ?: $trabajo->titulo }}">
                            <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][titulo]" value="{{ $imagen->titulo }}">
                            <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][descripcion]" value="{{ $imagen->descripcion }}">
                            <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][estado]" value="{{ $imagen->estado ? 1 : 0 }}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <label class="form-label mb-0">Agregar imagenes</label>
                <span class="badge bg-info-subtle text-info">JPG, PNG, WEBP - max. 4 MB c/u</span>
            </div>
            <label class="mi-portafolio-upload-zone">
                <input type="file" name="imagenes[]" class="js-mi-portafolio-file-input @error('imagenes') is-invalid @enderror @error('imagenes.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp" multiple>
                <span class="mi-portafolio-upload-icon">
                    <i class="ri-image-add-line"></i>
                </span>
                <strong>Selecciona nuevas imagenes</strong>
                <small class="text-muted">Opcional. Si no eliges una, se mantienen las actuales.</small>
                <span class="mi-portafolio-file-name">Sin nuevas imagenes</span>
            </label>
            <input type="hidden" name="imagenes_titulo[]" value="Evidencia">
            <input type="hidden" name="imagenes_descripcion[]" value="">
            @error('imagenes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('imagenes.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line align-bottom me-1"></i>
                Guardar cambios
            </button>
        </div>
    </form>
</div>
