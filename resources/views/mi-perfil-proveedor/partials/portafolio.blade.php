<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mi portafolio</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->portafolio->count() }} trabajos</span>
</div>

@can('gestionar portafolio proveedor')
    <form class="needs-validation border rounded p-3 mb-3" novalidate method="POST" action="{{ route('mi-perfil-proveedor.portafolio.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Titulo <span class="text-danger">*</span></label>
                <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" required>
                @error('titulo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa el titulo.</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha del trabajo</label>
                <input type="date" name="fecha_trabajo" class="form-control @error('fecha_trabajo') is-invalid @enderror" value="{{ old('fecha_trabajo') }}">
                @error('fecha_trabajo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Descripcion</label>
                <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Imagen</label>
                <input type="file" name="imagenes[]" class="form-control js-mi-imagen-preview-input @error('imagenes') is-invalid @enderror @error('imagenes.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp" required>
                <input type="hidden" name="imagenes_titulo[]" value="Evidencia">
                <input type="hidden" name="imagenes_descripcion[]" value="">
                @error('imagenes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('imagenes.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona una imagen.</div>
                @enderror
            </div>
            <div class="col-md-8">
                <img src="" alt="Vista previa" class="img-fluid rounded border d-none js-mi-imagen-preview" style="max-height: 140px;">
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Agregar trabajo</button>
            </div>
        </div>
    </form>
@endcan

<div class="row g-3">
    @forelse ($perfilProveedor->portafolio as $trabajo)
        <div class="col-12">
            <div class="border rounded p-3">
                @can('gestionar portafolio proveedor')
                    <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.portafolio.update', $trabajo->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-lg-4">
                                @if ($trabajo->imagenes->first())
                                    <img src="{{ asset($trabajo->imagenes->first()->imagen) }}" alt="Trabajo realizado" class="img-fluid rounded border mb-2" style="height: 180px; width: 100%; object-fit: cover;">
                                @endif
                                <input type="file" name="imagenes[]" class="form-control form-control-sm js-mi-imagen-preview-input @error('imagenes') is-invalid @enderror @error('imagenes.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                                <input type="hidden" name="imagenes_titulo[]" value="Evidencia">
                                <input type="hidden" name="imagenes_descripcion[]" value="">
                                @error('imagenes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('imagenes.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <img src="" alt="Vista previa" class="img-fluid rounded border d-none mt-2 js-mi-imagen-preview" style="max-height: 120px;">
                            </div>
                            <div class="col-lg-8">
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label">Titulo</label>
                                        <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $trabajo->titulo) }}" required>
                                        @error('titulo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">Por favor ingresa el titulo.</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" name="fecha_trabajo" class="form-control @error('fecha_trabajo') is-invalid @enderror" value="{{ old('fecha_trabajo', optional($trabajo->fecha_trabajo)->format('Y-m-d')) }}">
                                        @error('fecha_trabajo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Descripcion</label>
                                        <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $trabajo->descripcion) }}</textarea>
                                        @error('descripcion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @foreach ($trabajo->imagenes as $imagen)
                                        <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][titulo]" value="{{ $imagen->titulo }}">
                                        <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][descripcion]" value="{{ $imagen->descripcion }}">
                                        <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][estado]" value="{{ $imagen->estado ? 1 : 0 }}">
                                    @endforeach
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-soft-primary">Actualizar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('mi-perfil-proveedor.portafolio.destroy', $trabajo->id) }}" class="js-confirm-delete-form mt-2 text-end">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-soft-danger">Eliminar trabajo</button>
                    </form>
                @else
                    <div class="row g-3">
                        <div class="col-md-4">
                            @if ($trabajo->imagenes->first())
                                <img src="{{ asset($trabajo->imagenes->first()->imagen) }}" alt="Trabajo realizado" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h6 class="mb-1">{{ $trabajo->titulo }}</h6>
                            <p class="text-muted mb-2">{{ $trabajo->descripcion }}</p>
                            <small class="text-muted">{{ optional($trabajo->fecha_trabajo)->format('d/m/Y') ?: 'Sin fecha' }}</small>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-4">Aun no tienes trabajos en tu portafolio.</div>
        </div>
    @endforelse
</div>
