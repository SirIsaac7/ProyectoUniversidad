@extends('layouts.app')

@section('title', 'Mis horarios')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mis horarios</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="mi-horarios-dashboard">
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card mi-horario-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-horario-stat-icon bg-primary-subtle text-primary">
                            <i class="ri-time-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Horas disponibles esta semana</p>
                            <h3 class="mb-1">{{ $horasSemana }} h{{ $minutosRestantes ? ' ' . $minutosRestantes . ' min' : '' }}</h3>
                            <small class="text-muted">Segun horarios activos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-horario-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-horario-stat-icon bg-info-subtle text-info">
                            <i class="ri-calendar-check-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Dias disponibles</p>
                            <h3 class="mb-1">{{ $diasDisponibles }} dias</h3>
                            <small class="text-muted">
                                {{ $horariosDisponibles->pluck('dia_semana')->unique()->sort()->map(fn ($dia) => $diasCortos[$dia] ?? '')->filter()->join(' - ') ?: 'Sin dias' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-horario-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-horario-stat-icon bg-secondary-subtle text-secondary">
                            <i class="ri-calendar-event-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Citas programadas</p>
                            <h3 class="mb-1">0</h3>
                            <small class="text-muted">Modulo pendiente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card mi-horario-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="mi-horario-stat-icon bg-warning-subtle text-warning">
                            <i class="ri-cup-line"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-medium mb-1">Proximo descanso</p>
                            <h3 class="mb-1 fs-20">{{ $proximoDescanso ? $diasSemana[$proximoDescanso->dia_semana] : 'Sin descanso' }}</h3>
                            <small class="text-muted">{{ $proximoDescanso ? 'Dia no disponible' : 'No configurado' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <div
                        id="miHorariosCalendar"
                        data-horarios='@json($horariosCalendario)'
                    ></div>

                    <div class="mi-horario-legend mt-3">
                        <span><i class="ri-checkbox-blank-circle-fill text-success"></i> Disponible</span>
                        <span><i class="ri-checkbox-blank-circle-fill text-muted"></i> No disponible</span>
                        <span><i class="ri-checkbox-blank-circle-fill text-primary"></i> Cita agendada</span>
                        <span><i class="ri-checkbox-blank-circle-fill text-warning"></i> Descanso / feriado</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <span class="border-start border-primary border-3 ps-2">Agregar horario</span>
                    </h5>

                    @include('proveedor.horarios.partials.create')
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Horarios habituales</h5>
                        <span class="badge bg-primary-subtle text-primary">{{ $horarios->count() }}</span>
                    </div>

                    <div class="vstack gap-2 mi-horarios-habituales-list">
                        @forelse ($horarios as $horario)
                            <div class="mi-horario-item" id="mi-horario-{{ $horario->id }}">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $diasSemana[$horario->dia_semana] ?? 'Sin dia' }}</div>
                                        <small class="text-muted">
                                            @if ($horario->disponible)
                                                {{ optional($horario->hora_inicio)->format('H:i') }} - {{ optional($horario->hora_fin)->format('H:i') }}
                                            @else
                                                No disponible
                                            @endif
                                        </small>
                                    </div>

                                    @can('gestionar horarios proveedor')
                                        <button
                                            class="btn btn-sm mi-horario-edit-btn"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#editar-horario-{{ $horario->id }}"
                                            aria-expanded="false"
                                            aria-controls="editar-horario-{{ $horario->id }}"
                                            title="Editar"
                                        >
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                    @endcan
                                </div>

                                @include('proveedor.horarios.partials.edit')
                            </div>
                        @empty
                            <div class="mi-perfil-empty-state">
                                <span class="mi-perfil-empty-icon bg-primary-subtle text-primary">
                                    <i class="ri-calendar-schedule-line"></i>
                                </span>
                                <h5>Aun no tienes horarios</h5>
                                <p>Agrega tus dias y horas de atencion para que los clientes sepan cuando puedes atender.</p>
                                @can('gestionar horarios proveedor')
                                    <button type="button" class="btn btn-primary btn-sm" id="miHorarioOpenEmptyForm">
                                        <i class="ri-add-line align-bottom me-1"></i>
                                        Agregar horario
                                    </button>
                                @endcan
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/miPerfilProveedorHorarios.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/fullcalendar/index.global.min.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedorHorarios.js') }}"></script>
@endpush
