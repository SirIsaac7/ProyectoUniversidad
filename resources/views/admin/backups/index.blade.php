@extends('layouts.app')

@section('title', 'Backups')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <div>
                <h4 class="mb-1">Backups</h4>
                <p class="text-muted mb-0">Gestiona copias de seguridad manuales y automaticas.</p>
            </div>

            @can('ejecutar backups')
                <form method="POST" action="{{ route('backups.run') }}" class="js-confirm-submit"
                    data-confirm-title="Ejecutar backup"
                    data-confirm-text="Se creara una copia de seguridad ahora.">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line align-bottom me-1"></i>
                        Ejecutar backup
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Programacion automatica</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('backups.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Hora de ejecucion</label>
                        <input type="time" name="hora_ejecucion"
                            class="form-control @error('hora_ejecucion') is-invalid @enderror"
                            value="{{ old('hora_ejecucion', substr($configuracion->hora_ejecucion, 0, 5)) }}">
                        @error('hora_ejecucion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Frecuencia</label>
                        <select name="frecuencia" class="form-select @error('frecuencia') is-invalid @enderror">
                            <option value="diario" @selected(old('frecuencia', $configuracion->frecuencia) === 'diario')>Diario</option>
                            <option value="semanal" @selected(old('frecuencia', $configuracion->frecuencia) === 'semanal')>Semanal</option>
                            <option value="mensual" @selected(old('frecuencia', $configuracion->frecuencia) === 'mensual')>Mensual</option>
                        </select>
                        @error('frecuencia')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Dia de semana</label>
                            <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror">
                                <option value="">Selecciona</option>
                                <option value="1" @selected(old('dia_semana', $configuracion->dia_semana) == 1)>Lunes</option>
                                <option value="2" @selected(old('dia_semana', $configuracion->dia_semana) == 2)>Martes</option>
                                <option value="3" @selected(old('dia_semana', $configuracion->dia_semana) == 3)>Miercoles</option>
                                <option value="4" @selected(old('dia_semana', $configuracion->dia_semana) == 4)>Jueves</option>
                                <option value="5" @selected(old('dia_semana', $configuracion->dia_semana) == 5)>Viernes</option>
                                <option value="6" @selected(old('dia_semana', $configuracion->dia_semana) == 6)>Sabado</option>
                                <option value="7" @selected(old('dia_semana', $configuracion->dia_semana) == 7)>Domingo</option>
                            </select>
                            @error('dia_semana')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Dia del mes</label>
                            <input type="number" name="dia_mes" min="1" max="28"
                                class="form-control @error('dia_mes') is-invalid @enderror"
                                value="{{ old('dia_mes', $configuracion->dia_mes) }}">
                            @error('dia_mes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" value="1"
                            id="activo" @checked(old('activo', $configuracion->activo))>
                        <label class="form-check-label" for="activo">Backup automatico activo</label>
                    </div>

                    @can('configurar backups')
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="ri-check-line align-bottom me-1"></i>
                                Guardar configuracion
                            </button>
                        </div>
                    @endcan
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Backups generados</h5>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-{{ $configuracion->ultimo_estado === 'correcto' ? 'success' : 'secondary' }}-subtle text-{{ $configuracion->ultimo_estado === 'correcto' ? 'success' : 'secondary' }}">
                        Ultimo estado: {{ $configuracion->ultimo_estado ?? 'Sin ejecucion' }}
                    </span>
                    @if ($configuracion->ultimo_backup_at)
                        <span class="badge bg-info-subtle text-info">
                            {{ $configuracion->ultimo_backup_at->format('d/m/Y H:i') }}
                        </span>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Archivo</th>
                                <th>Tamano</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backups as $backup)
                                <tr>
                                    <td>{{ $backup['nombre'] }}</td>
                                    <td>{{ number_format($backup['tamano'] / 1024 / 1024, 2) }} MB</td>
                                    <td>{{ date('d/m/Y H:i', $backup['fecha']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Aun no hay backups generados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-muted mt-3 mb-0">
                    Ruta local: <code>storage/app/backups</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
