@php
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'];
    $tiposAtencion = ['mixto' => 'Mixto', 'domicilio' => 'Domicilio', 'local' => 'Local', 'remoto' => 'Remoto'];
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mis horarios</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->horarios->count() }} registrados</span>
</div>

@can('gestionar horarios proveedor')
    <form class="needs-validation border rounded p-3 mb-3" novalidate method="POST" action="{{ route('mi-perfil-proveedor.horarios.store') }}">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Dia</label>
                <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                    <option value="">Dia</option>
                    @foreach ($diasSemana as $numeroDia => $dia)
                        <option value="{{ $numeroDia }}">{{ $dia }}</option>
                    @endforeach
                </select>
                @error('dia_semana')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona el dia.</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Inicio</label>
                <input type="time" name="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror" value="{{ old('hora_inicio') }}" required>
                @error('hora_inicio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa la hora de inicio.</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Fin</label>
                <input type="time" name="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror" value="{{ old('hora_fin') }}" required>
                @error('hora_fin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor ingresa la hora de fin.</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Atencion</label>
                <select name="tipo_atencion" class="form-select @error('tipo_atencion') is-invalid @enderror" required>
                    @foreach ($tiposAtencion as $valor => $texto)
                        <option value="{{ $valor }}">{{ $texto }}</option>
                    @endforeach
                </select>
                @error('tipo_atencion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Disponible</label>
                <select name="disponible" class="form-select @error('disponible') is-invalid @enderror" required>
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
                @error('disponible')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona la disponibilidad.</div>
                @enderror
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Agregar</button></div>
        </div>
    </form>
@endcan

<div class="vstack gap-2">
    @forelse ($perfilProveedor->horarios as $horario)
        <div class="border rounded p-3">
            @can('gestionar horarios proveedor')
                <form class="needs-validation" novalidate method="POST" action="{{ route('mi-perfil-proveedor.horarios.update', $horario->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Dia</label>
                            <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                                @foreach ($diasSemana as $numeroDia => $dia)
                                    <option value="{{ $numeroDia }}" @selected($horario->dia_semana === $numeroDia)>{{ $dia }}</option>
                                @endforeach
                            </select>
                            @error('dia_semana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona el dia.</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Inicio</label>
                            <input type="time" name="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror" value="{{ old('hora_inicio', optional($horario->hora_inicio)->format('H:i')) }}" required>
                            @error('hora_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa la hora de inicio.</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fin</label>
                            <input type="time" name="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror" value="{{ old('hora_fin', optional($horario->hora_fin)->format('H:i')) }}" required>
                            @error('hora_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa la hora de fin.</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Atencion</label>
                            <select name="tipo_atencion" class="form-select @error('tipo_atencion') is-invalid @enderror" required>
                                @foreach ($tiposAtencion as $valor => $texto)
                                    <option value="{{ $valor }}" @selected($horario->tipo_atencion === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('tipo_atencion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Disponible</label>
                            <select name="disponible" class="form-select @error('disponible') is-invalid @enderror" required>
                                <option value="1" @selected($horario->disponible)>Si</option>
                                <option value="0" @selected(! $horario->disponible)>No</option>
                            </select>
                            @error('disponible')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona la disponibilidad.</div>
                            @enderror
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-soft-primary w-100">Guardar</button></div>
                    </div>
                </form>
                <form method="POST" action="{{ route('mi-perfil-proveedor.horarios.destroy', $horario->id) }}" class="js-confirm-delete-form mt-2 text-end">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-soft-danger">Eliminar</button>
                </form>
            @else
                <div class="d-flex justify-content-between">
                    <div>{{ $diasSemana[$horario->dia_semana] ?? 'Sin dia' }}: {{ optional($horario->hora_inicio)->format('H:i') }} - {{ optional($horario->hora_fin)->format('H:i') }}</div>
                    <span class="badge {{ $horario->disponible ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $horario->disponible ? 'Disponible' : 'No disponible' }}</span>
                </div>
            @endcan
        </div>
    @empty
        <div class="text-center text-muted py-4">Aun no tienes horarios registrados.</div>
    @endforelse
</div>
