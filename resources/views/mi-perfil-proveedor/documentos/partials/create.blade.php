<div class="mi-documento-side-panel {{ $errors->any() && ! old('documento_id') ? '' : 'd-none' }}" id="miDocumentoCreatePanel">
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="mi-documento-panel-icon bg-primary-subtle text-primary">
            <i class="ri-upload-2-line"></i>
        </span>
        <div>
            <h5 class="mb-1">Subir documento</h5>
            <small class="text-muted">Agrega un archivo para revision administrativa.</small>
        </div>
    </div>

    <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.documentos.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
            <select name="tipo_documento_proveedor_id" class="form-select js-mi-documento-choices @error('tipo_documento_proveedor_id') is-invalid @enderror" required>
                <option value="">Selecciona un documento</option>
                @foreach ($tiposDocumentoDisponibles as $tipoDocumento)
                    <option value="{{ $tipoDocumento->id }}" @selected(old('tipo_documento_proveedor_id') == $tipoDocumento->id)>
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
            <label class="form-label">Archivo <span class="text-danger">*</span></label>
            <label class="mi-documento-upload-zone">
                <input type="file" name="archivo" class="js-mi-documento-file-input @error('archivo') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                <span class="mi-documento-upload-icon">
                    <i class="ri-file-upload-line"></i>
                </span>
                <strong>Selecciona o arrastra un archivo</strong>
                <small class="text-muted">PDF, JPG, PNG o WEBP hasta 5 MB.</small>
                <span class="mi-documento-file-name">Ningun archivo seleccionado</span>
            </label>
            @error('archivo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Por favor selecciona un archivo.</div>
            @enderror
        </div>

        <div class="mi-documento-form-note mb-4">
            <span class="badge bg-warning-subtle text-warning mb-2">Revision pendiente</span>
            <p class="mb-0">Al subirlo, el documento quedara pendiente hasta que administracion lo revise.</p>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line align-bottom me-1"></i>
                Subir documento
            </button>
        </div>
    </form>
</div>
