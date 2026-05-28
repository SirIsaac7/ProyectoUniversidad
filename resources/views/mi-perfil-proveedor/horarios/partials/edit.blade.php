@can('gestionar horarios proveedor')
    <div class="collapse mt-3" id="editar-horario-{{ $horario->id }}">
        <div class="mi-horario-edit-panel">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="mi-horario-edit-heading-icon">
                        <i class="ri-calendar-check-line"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Editar {{ $diasSemana[$horario->dia_semana] ?? 'horario' }}</h5>
                        <span class="mi-horario-edit-underline"></span>
                    </div>
                </div>

            </div>

            <div class="row g-3">
                <div class="col-12">
                    <form id="form-editar-horario-{{ $horario->id }}" class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.horarios.update', $horario->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Dia de la semana <span class="text-danger">*</span></label>
                            <div class="mi-horario-input-icon">
                                <i class="ri-calendar-line"></i>
                                <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                                    @foreach ($diasSemana as $numeroDia => $dia)
                                        <option value="{{ $numeroDia }}" @selected(old('dia_semana', $horario->dia_semana) == $numeroDia)>{{ $dia }}</option>
                                    @endforeach
                                </select>
                                @error('dia_semana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Por favor selecciona el dia.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hora de inicio <span class="text-danger">*</span></label>
                                <div class="mi-horario-input-icon">
                                    <i class="ri-time-line"></i>
                                    <input type="time" name="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror" value="{{ old('hora_inicio', optional($horario->hora_inicio)->format('H:i')) }}" required>
                                    @error('hora_inicio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor ingresa la hora de inicio.</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Hora de fin <span class="text-danger">*</span></label>
                                <div class="mi-horario-input-icon">
                                    <i class="ri-time-line"></i>
                                    <input type="time" name="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror" value="{{ old('hora_fin', optional($horario->hora_fin)->format('H:i')) }}" required>
                                    @error('hora_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor ingresa la hora de fin.</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de atención <span class="text-danger">*</span></label>
                                <div>
                                    <select name="tipo_atencion" class="form-select @error('tipo_atencion') is-invalid @enderror" required>
                                        @foreach ($tiposAtencion as $valor => $texto)
                                            <option value="{{ $valor }}" @selected(old('tipo_atencion', $horario->tipo_atencion) === $valor)>{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipo_atencion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <div>
                                    <select name="disponible" class="form-select @error('disponible') is-invalid @enderror" required>
                                        <option value="1" @selected(old('disponible', (string) (int) $horario->disponible) === '1')>Disponible</option>
                                        <option value="0" @selected(old('disponible', (string) (int) $horario->disponible) === '0')>No disponible</option>
                                    </select>
                                    @error('disponible')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor selecciona la disponibilidad.</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <div class="alert mi-horario-edit-info mt-3 mb-0" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <span class="badge {{ $horario->disponible ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                        {{ $horario->disponible ? 'Disponible' : 'No disponible' }}
                    </span>
                    <div>
                        <strong>{{ $horario->disponible ? 'Visible para clientes' : 'Oculto como horario disponible' }}</strong>
                        <p class="mb-0">{{ $horario->disponible ? 'Este horario podra tomarse en cuenta para mostrar tu disponibilidad.' : 'Este dia o rango quedara marcado como no disponible.' }}</p>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between gap-2 mt-3">
                <form method="POST" action="{{ route('mi-perfil-proveedor.horarios.destroy', $horario->id) }}" class="js-confirm-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-soft-danger">
                        <i class="ri-delete-bin-line align-bottom me-1"></i>
                        Eliminar
                    </button>
                </form>

                <button type="submit" class="btn btn-primary" form="form-editar-horario-{{ $horario->id }}">
                    <i class="ri-save-line align-bottom me-1"></i>
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>
@endcan
