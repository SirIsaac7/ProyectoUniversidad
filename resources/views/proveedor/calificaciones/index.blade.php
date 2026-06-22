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
                    <h4 class="mb-1">Calificaciones</h4>
                    <p class="text-muted mb-0">Gestiona las opiniones de tus clientes y responde sus comentarios.</p>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="d-none" id="calificaciones-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @include('proveedor.calificaciones.partials.resumen', ['resumenCalificaciones' => $resumenCalificaciones])

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card calificacion-panel-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Reseñas recibidas</h5>
                    <span class="badge bg-primary-subtle text-primary">{{ $calificaciones->total() }} reseñas</span>
                </div>
                <div class="card-body">
                    @forelse ($calificaciones as $calificacion)
                        @include('proveedor.calificaciones.partials.card-calificacion', ['calificacion' => $calificacion])
                    @empty
                        <div class="text-center py-5">
                            <div class="avatar-md mx-auto mb-3">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    <i class="ri-star-smile-line fs-24"></i>
                                </span>
                            </div>
                            <h5 class="mb-2">Aun no tienes calificaciones</h5>
                            <p class="text-muted mb-0">Cuando completes citas y tus clientes califiquen, apareceran aqui.</p>
                        </div>
                    @endforelse

                    <div class="mt-3">
                        {{ $calificaciones->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card calificacion-panel-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Respuestas pendientes</h5>
                    <span class="badge bg-warning-subtle text-warning">{{ $respuestasPendientes->count() }} pendientes</span>
                </div>
                <div class="card-body">
                    @forelse ($respuestasPendientes as $pendiente)
                        <div class="calificacion-pending-item">
                            <div>
                                <h6 class="mb-1">{{ $pendiente->cita?->solicitud?->cliente?->name ?? 'Cliente' }}</h6>
                                <small class="text-muted">
                                    {{ $pendiente->cita?->fecha_cita?->format('d/m/Y') ?? 'Sin fecha' }}
                                    -
                                    {{ $pendiente->cita?->solicitud?->especialidad?->nombre ?? 'Sin especialidad' }}
                                </small>
                            </div>
                            <a href="#calificacion-{{ $pendiente->id }}" class="btn btn-sm btn-soft-primary">
                                <i class="ri-arrow-right-s-line"></i>
                            </a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No tienes respuestas pendientes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/calificaciones.js') }}"></script>
@endpush
