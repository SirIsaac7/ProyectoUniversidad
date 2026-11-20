@extends('layouts.app')

@section('title', 'Programación de indexación')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div id="motorBusquedaPanel">
    <div class="page-title-box bg-transparent">
        <h4 class="mb-1">Programación automática</h4>
        <p class="text-muted mb-0">Configura las indexaciones automáticas en la zona horaria America/La_Paz.</p>
    </div>

    <section class="card mb-4" aria-labelledby="programacionIndexacionTitulo">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0" id="programacionIndexacionTitulo">Programación de indexación</h5>
            <span class="badge {{ $configuracion->activo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                {{ $configuracion->activo ? 'Programación activa' : 'Programación desactivada' }}
            </span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('motor-busqueda.programacion.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <div class="form-check form-switch motor-program-switch">
                            <input type="hidden" name="activo" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="programacionActiva" name="activo" value="1" @checked(old('activo', $configuracion->activo))>
                            <label class="form-check-label" for="programacionActiva">Indexación automática</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="frecuenciaIndexacion">Frecuencia</label>
                        <select class="form-select @error('frecuencia') is-invalid @enderror" id="frecuenciaIndexacion" name="frecuencia">
                            <option value="diario" @selected(old('frecuencia', $configuracion->frecuencia) === 'diario')>Diaria</option>
                            <option value="semanal" @selected(old('frecuencia', $configuracion->frecuencia) === 'semanal')>Semanal</option>
                        </select>
                        @error('frecuencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2" id="diaSemanaIndexacionWrapper">
                        <label class="form-label" for="diaSemanaIndexacion">Día</label>
                        <select class="form-select @error('dia_semana') is-invalid @enderror" id="diaSemanaIndexacion" name="dia_semana">
                            <option value="">Selecciona</option>
                            @foreach ([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $valor => $dia)
                                <option value="{{ $valor }}" @selected((int) old('dia_semana', $configuracion->dia_semana) === $valor)>{{ $dia }}</option>
                            @endforeach
                        </select>
                        @error('dia_semana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="horaIndexacion">Hora</label>
                        <input type="time" class="form-control @error('hora_ejecucion') is-invalid @enderror" id="horaIndexacion" name="hora_ejecucion" value="{{ old('hora_ejecucion', substr($configuracion->hora_ejecucion, 0, 5)) }}" required>
                        @error('hora_ejecucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100"><i class="ri-save-line align-bottom me-1"></i>Guardar</button>
                    </div>
                </div>
            </form>

            <div class="motor-next-run mt-3">
                <i class="ri-calendar-schedule-line"></i>
                <div>
                    <small>Próxima ejecución</small>
                    <strong>{{ $proximaEjecucion ? $proximaEjecucion->format('d/m/Y H:i').' (America/La_Paz)' : 'No programada' }}</strong>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/motorBusquedaAdmin.js') }}"></script>
@endpush
