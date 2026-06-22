@extends('layouts.app')

@section('title', 'Mis calificaciones')

@push('styles')
<link href="{{ asset('assets/css/calificaciones.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="calificaciones-page">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Mis calificaciones</h4>
                    <p class="text-muted mb-0">Califica tus citas completadas y revisa tus resenas registradas.</p>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="d-none" id="calificaciones-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @include('cliente.calificaciones.partials.resumen', ['resumenCalificaciones' => $resumenCalificaciones])

    <div class="row g-3">
        <div class="col-xl-5">
            <div class="card calificacion-panel-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Resenas realizadas</h5>
                    <span class="badge bg-primary-subtle text-primary">{{ $calificaciones->total() }} registradas</span>
                </div>
                <div class="card-body">
                    @forelse ($calificaciones as $calificacion)
                        @include('cliente.calificaciones.partials.card-calificacion', ['calificacion' => $calificacion])
                    @empty
                        <div class="text-center py-5">
                            <div class="avatar-md mx-auto mb-3">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    <i class="ri-star-line fs-24"></i>
                                </span>
                            </div>
                            <h5 class="mb-2">Aun no realizaste calificaciones</h5>
                            <p class="text-muted mb-0">Tus resenas apareceran en esta seccion.</p>
                        </div>
                    @endforelse

                    <div class="mt-3">
                        {{ $calificaciones->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card calificacion-panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Citas por calificar</h5>
                    <span class="badge bg-warning-subtle text-warning">{{ $citasPendientesCalificacion->count() }} pendientes</span>
                </div>
                <div class="card-body">
                    @forelse ($citasPendientesCalificacion as $cita)
                        <div class="calificacion-review-card cliente-cita-calificacion-card">
                            <button
                                type="button"
                                class="cliente-cita-calificacion-summary js-calificacion-cita-option"
                                data-target="calificacionForm{{ $cita->id }}"
                            >
                                <span class="avatar-md flex-shrink-0">
                                    <span class="avatar-title rounded bg-primary-subtle text-primary fs-22">
                                        <i class="ri-calendar-check-line"></i>
                                    </span>
                                </span>

                                <span class="flex-grow-1 text-start">
                                    <span class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <span>
                                            <strong class="d-block fs-16">{{ $cita->solicitud?->titulo ?? 'Cita completada' }}</strong>
                                            <small class="text-muted">{{ $cita->solicitud?->perfilProveedor?->nombre_publico ?? 'Proveedor no disponible' }}</small>
                                        </span>
                                        <span class="badge bg-success-subtle text-success">Completada</span>
                                    </span>
                                    <span class="d-flex flex-wrap gap-3 text-muted">
                                        <small>
                                            <i class="ri-calendar-line align-bottom me-1"></i>
                                            {{ $cita->fecha_cita?->format('d/m/Y') ?? 'Sin fecha' }}
                                        </small>
                                        <small>
                                            <i class="ri-tools-line align-bottom me-1"></i>
                                            {{ $cita->solicitud?->especialidad?->nombre ?? 'Sin especialidad' }}
                                        </small>
                                    </span>
                                </span>

                                <i class="ri-arrow-down-s-line fs-22 cliente-cita-calificacion-arrow"></i>
                            </button>

                            <div class="cliente-cita-calificacion-detail d-none" id="calificacionForm{{ $cita->id }}">
                                <form
                                    method="POST"
                                    action="{{ route('cliente.calificaciones.store') }}"
                                    class="calificacion-form calificacion-wizard-form needs-validation"
                                    novalidate
                                    data-calificacion-wizard
                                >
                                    @csrf
                                    <input type="hidden" name="cita_id" value="{{ $cita->id }}">

                                    <div class="calificacion-wizard-header">
                                        <div class="calificacion-wizard-step is-active" data-step-indicator="1">
                                            <span>1</span>
                                            <strong>General</strong>
                                        </div>
                                        <div class="calificacion-wizard-step" data-step-indicator="2">
                                            <span>2</span>
                                            <strong>Aspectos</strong>
                                        </div>
                                        <div class="calificacion-wizard-step" data-step-indicator="3">
                                            <span>3</span>
                                            <strong>Comentario</strong>
                                        </div>
                                    </div>

                                    <div class="calificacion-wizard-pane is-active" data-step="1">
                                        <h5 class="mb-1">Calificacion general</h5>
                                        <p class="text-muted mb-3">Selecciona una puntuacion general para esta cita.</p>

                                        <input type="hidden" name="puntuacion" class="js-star-value" required>
                                        <div class="calificacion-star-picker" data-star-group>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button type="button" class="calificacion-star-button" data-value="{{ $i }}" aria-label="{{ $i }} estrellas">
                                                    <i class="ri-star-fill"></i>
                                                </button>
                                            @endfor
                                        </div>
                                        <div class="invalid-feedback d-block d-none js-star-error">Selecciona una puntuacion general.</div>
                                    </div>

                                    <div class="calificacion-wizard-pane" data-step="2">
                                        <h5 class="mb-1">Calificacion por aspecto</h5>
                                        <p class="text-muted mb-3">Evalua cada aspecto del servicio usando estrellas.</p>

                                        @if ($aspectosCalificacion->isNotEmpty())
                                            <div class="calificacion-aspect-list">
                                                @foreach ($aspectosCalificacion as $aspectoCalificacion)
                                                    <div class="calificacion-aspect-card">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                                            <div>
                                                                <h6 class="mb-1">{{ $aspectoCalificacion->nombre }}</h6>
                                                                <small class="text-muted">{{ $aspectoCalificacion->descripcion ?: 'Evalua este aspecto del servicio.' }}</small>
                                                            </div>
                                                            <input type="hidden" name="aspectos[{{ $aspectoCalificacion->id }}]" class="js-star-value" required>
                                                            <div class="calificacion-star-picker calificacion-star-picker-sm" data-star-group>
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    <button type="button" class="calificacion-star-button" data-value="{{ $i }}" aria-label="{{ $i }} estrellas">
                                                                        <i class="ri-star-fill"></i>
                                                                    </button>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                        <div class="invalid-feedback d-block d-none js-star-error">Califica este aspecto.</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-0">No hay aspectos activos para calificar.</div>
                                        @endif
                                    </div>

                                    <div class="calificacion-wizard-pane" data-step="3">
                                        <h5 class="mb-1">Comentario final</h5>
                                        <p class="text-muted mb-3">Deja una opinion general sobre la atencion recibida.</p>

                                        <textarea
                                            name="comentario"
                                            rows="5"
                                            class="form-control @error('comentario') is-invalid @enderror"
                                            maxlength="1500"
                                            placeholder="Cuenta como fue la atencion recibida."
                                        >{{ old('comentario') }}</textarea>
                                        @error('comentario')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-between gap-2 mt-4">
                                        <button type="button" class="btn btn-light js-wizard-prev" disabled>
                                            <i class="ri-arrow-left-line align-bottom me-1"></i>
                                            Anterior
                                        </button>

                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary js-wizard-next">
                                                Siguiente
                                                <i class="ri-arrow-right-line align-bottom ms-1"></i>
                                            </button>

                                            @can('crear mis calificaciones')
                                                <button type="submit" class="btn btn-success js-wizard-submit d-none">
                                                    <i class="ri-star-smile-line align-bottom me-1"></i>
                                                    Registrar calificacion
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="avatar-md mx-auto mb-3">
                                <span class="avatar-title rounded-circle bg-success-subtle text-success">
                                    <i class="ri-checkbox-circle-line fs-24"></i>
                                </span>
                            </div>
                            <h5 class="mb-2">No tienes citas pendientes por calificar</h5>
                            <p class="text-muted mb-0">Cuando una cita sea completada, podras calificarla aqui.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/calificaciones.js') }}"></script>
@endpush
