<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            @if ($perfilProveedor->foto_portada)
                <img src="{{ asset($perfilProveedor->foto_portada) }}" alt="Foto de portada" class="img-fluid rounded mb-3" style="max-height: 180px; object-fit: cover;">
            @else
                <div class="avatar-lg mx-auto mb-3">
                    <span class="avatar-title rounded bg-primary-subtle text-primary fs-2">
                        <i class="ri-user-star-line"></i>
                    </span>
                </div>
            @endif

            <h5 class="mb-1">{{ $perfilProveedor->nombre_publico }}</h5>
            <p class="text-muted mb-0">{{ $perfilProveedor->user?->email }}</p>
        </div>

        @can('actualizar perfil proveedor')
        <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nombre_publico" class="form-label">
                    Nombre publico <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control @error('nombre_publico') is-invalid @enderror" id="nombre_publico" name="nombre_publico" value="{{ old('nombre_publico', $perfilProveedor->nombre_publico) }}" required>
                @error('nombre_publico')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa tu nombre publico.</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="anios_experiencia" class="form-label">Anios de experiencia</label>
                <input type="number" min="0" max="80" class="form-control @error('anios_experiencia') is-invalid @enderror" id="anios_experiencia" name="anios_experiencia" value="{{ old('anios_experiencia', $perfilProveedor->anios_experiencia) }}">
                @error('anios_experiencia')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="foto_portada" class="form-label">Foto de portada</label>
                <input type="file" class="form-control @error('foto_portada') is-invalid @enderror" id="foto_portada" name="foto_portada" accept=".jpg,.jpeg,.png,.webp">
                @error('foto_portada')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripcion</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="5">{{ old('descripcion', $perfilProveedor->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-info-subtle text-info">Verificacion: {{ ucfirst($perfilProveedor->estado_verificacion) }}</span>
                @if ($perfilProveedor->estado)
                    <span class="badge bg-success-subtle text-success">Activo</span>
                @else
                    <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                @endif
            </div>

            @if ($perfilProveedor->motivo_rechazo)
                <div class="alert alert-warning">
                    {{ $perfilProveedor->motivo_rechazo }}
                </div>
            @endif

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line align-bottom me-1"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
        @else
            <div class="alert alert-info mb-0">
                Puedes visualizar tu perfil, pero no tienes permiso para actualizarlo.
            </div>
        @endcan
    </div>
</div>
