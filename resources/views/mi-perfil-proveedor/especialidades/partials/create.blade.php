@can('gestionar especialidades proveedor')
    <div class="mi-especialidad-side-panel d-none" id="miEspecialidadCreatePanel">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="mi-especialidad-panel-icon bg-primary-subtle text-primary">
                <i class="ri-add-line"></i>
            </span>
            <div>
                <h5 class="mb-1">Agregar especialidad</h5>
                <small class="text-muted">Asigna una especialidad a tu perfil.</small>
            </div>
        </div>

        <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.especialidades.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Especialidad <span class="text-danger">*</span></label>
                <select name="especialidad_id" class="form-select js-mi-especialidad-choices @error('especialidad_id') is-invalid @enderror" required>
                    <option value="">Selecciona una especialidad</option>
                    @foreach ($especialidadesDisponibles as $especialidad)
                        <option value="{{ $especialidad->id }}" @selected(old('especialidad_id') == $especialidad->id)>
                            {{ $especialidad->rubroTipoServicio?->rubro?->nombre }} - {{ $especialidad->rubroTipoServicio?->tipoServicio?->nombre }} - {{ $especialidad->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('especialidad_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona una especialidad.</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Principal <span class="text-danger">*</span></label>
                <select name="es_principal" class="form-select @error('es_principal') is-invalid @enderror" required>
                    <option value="0" @selected(old('es_principal', '0') === '0')>No</option>
                    <option value="1" @selected(old('es_principal') === '1')>Si</option>
                </select>
                @error('es_principal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona si es principal.</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line align-bottom me-1"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endcan
