@php
    $extensionDocumento = strtolower(pathinfo($documento->archivo, PATHINFO_EXTENSION));
    $documentoEsImagen = in_array($extensionDocumento, ['jpg', 'jpeg', 'png', 'webp']);
@endphp

<div class="mi-documento-side-panel {{ $errors->any() && (int) old('documento_id') === $documento->id ? '' : 'd-none' }}" id="miDocumentoEditPanel{{ $documento->id }}">
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="mi-documento-panel-icon bg-warning-subtle text-warning">
            <i class="ri-pencil-line"></i>
        </span>
        <div>
            <h5 class="mb-1">Editar documento</h5>
            <small class="text-muted">{{ $documento->tipoDocumentoProveedor?->nombre }}</small>
        </div>
    </div>

    <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.documentos.update', $documento->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="documento_id" value="{{ $documento->id }}">

        <div class="mb-3">
            <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
            <select name="tipo_documento_proveedor_id" class="form-select js-mi-documento-choices @error('tipo_documento_proveedor_id') is-invalid @enderror" required>
                @foreach ($tiposDocumentoDisponibles as $tipoDocumento)
                    <option value="{{ $tipoDocumento->id }}" @selected(old('tipo_documento_proveedor_id', $documento->tipo_documento_proveedor_id) == $tipoDocumento->id)>
                        {{ $tipoDocumento->nombre }} {{ $tipoDocumento->obligatorio ? '(Obligatorio)' : '(Opcional)' }}
                    </option>
                @endforeach
            </select>
            @error('tipo_documento_proveedor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor selecciona el tipo de documento.</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Archivo actual</label>
            <a href="{{ asset($documento->archivo) }}" target="_blank" rel="noopener" class="mi-documento-current-file">
                <span class="mi-documento-file-icon {{ $documentoEsImagen ? 'is-image' : 'is-pdf' }}">
                    <i class="{{ $documentoEsImagen ? 'ri-image-line' : 'ri-file-pdf-2-line' }}"></i>
                </span>
                <span>
                    <strong>{{ basename($documento->archivo) }}</strong>
                    <small class="text-muted d-block">{{ strtoupper($extensionDocumento ?: 'Archivo') }}</small>
                </span>
            </a>
        </div>

        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <label class="form-label mb-0">Reemplazar archivo</label>
                <span class="badge bg-info-subtle text-info">PDF, JPG, JPEG,PNG, WEBP - max. 5 MB</span>
            </div>
            <label class="mi-documento-upload-zone">
                <input type="file" name="archivo" class="js-mi-documento-file-input @error('archivo') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                <span class="mi-documento-upload-icon">
                    <i class="ri-file-upload-line"></i>
                </span>
                <strong>Selecciona un nuevo archivo</strong>
                <small class="text-muted">Opcional. Si no eliges uno, se mantiene el actual.</small>
                <span class="mi-documento-file-name">Sin cambios en el archivo</span>
            </label>
            @error('archivo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mi-documento-form-note mb-4">
            <span class="badge bg-warning-subtle text-warning mb-2">Nueva revision</span>
            <p class="mb-0">Si actualizas el documento, volvera a estado pendiente.</p>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line align-bottom me-1"></i>
                Guardar cambios
            </button>
        </div>
    </form>
</div>
