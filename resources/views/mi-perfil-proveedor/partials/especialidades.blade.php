<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mis especialidades</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->proveedorEspecialidades->count() }} registradas</span>
</div>

@can('gestionar especialidades proveedor')
    <form class="needs-validation border rounded p-3 mb-3" novalidate method="POST" action="{{ route('mi-perfil-proveedor.especialidades.store') }}">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-lg-8">
                <label class="form-label">Especialidad <span class="text-danger">*</span></label>
                <select name="especialidad_id" class="form-select @error('especialidad_id') is-invalid @enderror" required>
                    <option value="">Selecciona una especialidad</option>
                    @foreach ($especialidadesDisponibles as $especialidad)
                        <option value="{{ $especialidad->id }}">{{ $especialidad->rubroTipoServicio?->rubro?->nombre }} - {{ $especialidad->rubroTipoServicio?->tipoServicio?->nombre }} - {{ $especialidad->nombre }}</option>
                    @endforeach
                </select>
                @error('especialidad_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona una especialidad.</div>
                @enderror
            </div>
            <div class="col-lg-2">
                <label class="form-label">Principal</label>
                <select name="es_principal" class="form-select @error('es_principal') is-invalid @enderror" required>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
                @error('es_principal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona si es principal.</div>
                @enderror
            </div>
            <div class="col-lg-2">
                <button type="submit" class="btn btn-primary w-100">Agregar</button>
            </div>
        </div>
    </form>
@endcan

<div class="vstack gap-2">
    @forelse ($perfilProveedor->proveedorEspecialidades as $proveedorEspecialidad)
        <div class="border rounded p-3">
            @can('gestionar especialidades proveedor')
                <form id="mi-especialidad-update-{{ $proveedorEspecialidad->id }}" class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.especialidades.update', $proveedorEspecialidad->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-7">
                            <label class="form-label">Especialidad</label>
                            <select name="especialidad_id" class="form-select @error('especialidad_id') is-invalid @enderror" required>
                                @foreach ($especialidadesDisponibles as $especialidad)
                                    <option value="{{ $especialidad->id }}" @selected($proveedorEspecialidad->especialidad_id === $especialidad->id)>{{ $especialidad->rubroTipoServicio?->rubro?->nombre }} - {{ $especialidad->rubroTipoServicio?->tipoServicio?->nombre }} - {{ $especialidad->nombre }}</option>
                                @endforeach
                            </select>
                            @error('especialidad_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona una especialidad.</div>
                            @enderror
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Principal</label>
                            <select name="es_principal" class="form-select @error('es_principal') is-invalid @enderror" required>
                                <option value="0" @selected(! $proveedorEspecialidad->es_principal)>No</option>
                                <option value="1" @selected($proveedorEspecialidad->es_principal)>Si</option>
                            </select>
                            @error('es_principal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona si es principal.</div>
                            @enderror
                        </div>
                        <div class="col-lg-3">
                            <button type="submit" class="btn btn-soft-primary w-100">Actualizar</button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('mi-perfil-proveedor.especialidades.destroy', $proveedorEspecialidad->id) }}" class="js-confirm-delete-form mt-2 text-end">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-soft-danger">Eliminar</button>
                </form>
            @else
                <div class="d-flex justify-content-between gap-2">
                    <div>
                        <div class="fw-semibold">{{ $proveedorEspecialidad->especialidad?->nombre }}</div>
                        <small class="text-muted">{{ $proveedorEspecialidad->especialidad?->rubroTipoServicio?->rubro?->nombre }} / {{ $proveedorEspecialidad->especialidad?->rubroTipoServicio?->tipoServicio?->nombre }}</small>
                    </div>
                    @if ($proveedorEspecialidad->es_principal)
                        <span class="badge bg-info-subtle text-info">Principal</span>
                    @endif
                </div>
            @endcan
        </div>
    @empty
        <div class="text-center text-muted py-4">Aun no tienes especialidades registradas.</div>
    @endforelse
</div>
