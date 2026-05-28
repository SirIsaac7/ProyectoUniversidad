@can('gestionar horarios proveedor')
    <div class="mi-horario-create-widget {{ $errors->any() ? 'is-open' : '' }}" id="miHorarioCreateWidget">
        <div class="mi-horario-create-intro">
            <div class="mi-horario-create-icon">
                <i class="ri-calendar-schedule-line"></i>
            </div>

            <span class="badge mi-horario-create-badge">
                Define tus horarios para que los clientes sepan cuando puedes atender.
            </span>

            <button type="button" class="btn btn-primary mt-3" id="miHorarioOpenForm">
                <i class="ri-add-line align-bottom me-1"></i>
                Agregar horario
            </button>
        </div>

        <form class="needs-validation mi-horario-create-form" novalidate method="POST" action="{{ route('mi-perfil-proveedor.horarios.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Dia <span class="text-danger">*</span></label>
                <select name="dia_semana" id="miHorarioDiaSemana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                    <option value="">Selecciona un dia</option>
                    @foreach ($diasSemana as $numeroDia => $dia)
                        <option value="{{ $numeroDia }}" @selected(old('dia_semana') == $numeroDia)>{{ $dia }}</option>
                    @endforeach
                </select>
                @error('dia_semana')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona el dia.</div>
                @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Hora inicio <span class="text-danger">*</span></label>
                    <input type="time" name="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror" value="{{ old('hora_inicio', '08:00') }}" required>
                    @error('hora_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa la hora de inicio.</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Hora fin <span class="text-danger">*</span></label>
                    <input type="time" name="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror" value="{{ old('hora_fin', '18:00') }}" required>
                    @error('hora_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa la hora de fin.</div>
                    @enderror
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Tipo de atencion <span class="text-danger">*</span></label>
                <select name="tipo_atencion" class="form-select @error('tipo_atencion') is-invalid @enderror" required>
                    @foreach ($tiposAtencion as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('tipo_atencion', 'mixto') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
                @error('tipo_atencion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                @enderror
            </div>

            <div class="mt-3">
                <label class="form-label">Disponible <span class="text-danger">*</span></label>
                <select name="disponible" class="form-select @error('disponible') is-invalid @enderror" required>
                    <option value="1" @selected(old('disponible', '1') === '1')>Disponible</option>
                    <option value="0" @selected(old('disponible') === '0')>No disponible</option>
                </select>
                @error('disponible')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona la disponibilidad.</div>
                @enderror
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line align-bottom me-1"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endcan
