<div class="card mi-perfil-form-card h-100 {{ $errors->any() ? '' : 'd-none' }}" id="miPerfilEditPanel">
    <div class="card-body">
        @can('actualizar perfil proveedor')
            <form class="needs-validation h-100 d-flex flex-column" novalidate method="POST" action="{{ route('mi-perfil-proveedor.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="file" class="d-none js-mi-perfil-cover-input @error('foto_portada') is-invalid @enderror" id="foto_portada" name="foto_portada" accept=".jpg,.jpeg,.png,.webp">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="mi-perfil-detail-icon bg-warning-subtle text-warning">
                        <i class="ri-pencil-line"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Editar perfil</h5>
                        <p class="text-muted mb-0">Actualiza los datos publicos de tu perfil proveedor.</p>
                    </div>
                </div>

                <div class="mi-perfil-form-grid">
                    <div class="mi-perfil-form-group">
                        <span class="mi-perfil-field-icon">
                            <i class="ri-user-line"></i>
                        </span>
                        <div class="w-100">
                            <label for="nombre_publico" class="form-label">Nombre publico <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_publico') is-invalid @enderror" id="nombre_publico" name="nombre_publico" value="{{ old('nombre_publico', $perfilProveedor->nombre_publico) }}" required>
                            @error('nombre_publico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa tu nombre publico.</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mi-perfil-form-group">
                        <span class="mi-perfil-field-icon">
                            <i class="ri-calendar-check-line"></i>
                        </span>
                        <div class="w-100">
                            <label for="anios_experiencia" class="form-label">Años de experiencia</label>
                            <input type="number" min="0" max="80" class="form-control @error('anios_experiencia') is-invalid @enderror" id="anios_experiencia" name="anios_experiencia" value="{{ old('anios_experiencia', $perfilProveedor->anios_experiencia) }}">
                            @error('anios_experiencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mi-perfil-form-group mi-perfil-form-group-full">
                        <span class="mi-perfil-field-icon">
                            <i class="ri-image-line"></i>
                        </span>
                        <div class="w-100">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <label for="foto_portada" class="form-label mb-0">Foto de portada</label>
                                <span class="badge bg-info-subtle text-info">JPG, PNG, WEBP - max. 4 MB</span>
                            </div>
                            <div class="mi-perfil-file-summary">
                                <span class="js-mi-perfil-file-name">{{ $perfilProveedor->foto_portada ? basename($perfilProveedor->foto_portada) : 'Sin foto seleccionada' }}</span>
                                <label for="foto_portada" class="btn btn-sm btn-soft-primary mb-0">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>
                                    Seleccionar
                                </label>
                            </div>
                            @error('foto_portada')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mi-perfil-form-group mi-perfil-form-group-full">
                        <span class="mi-perfil-field-icon">
                            <i class="ri-file-text-line"></i>
                        </span>
                        <div class="w-100">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="6">{{ old('descripcion', $perfilProveedor->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if ($perfilProveedor->motivo_rechazo)
                    <div class="alert alert-warning mt-3 mb-0">
                        {{ $perfilProveedor->motivo_rechazo }}
                    </div>
                @endif

                <div class="mi-perfil-form-actions mt-auto">
                    <button type="button" class="btn btn-soft-secondary js-mi-perfil-edit-cancel">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-bottom me-1"></i>
                        Guardar cambios
                    </button>
                </div>
            </form>
        @else
            <div class="mi-perfil-readonly">
                <span class="mi-perfil-readonly-icon bg-info-subtle text-info">
                    <i class="ri-eye-line"></i>
                </span>
                <h5>Solo lectura</h5>
                <p class="text-muted mb-0">Puedes visualizar tu perfil, pero no tienes permiso para actualizarlo.</p>
            </div>
        @endcan
    </div>
</div>
