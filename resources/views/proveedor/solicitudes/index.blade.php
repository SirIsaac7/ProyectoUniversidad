@extends('layouts.app')

@section('title', 'Solicitudes recibidas')

@push('styles')
<link href="{{ asset('assets/css/solicitudes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Solicitudes recibidas</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="solicitudes-success-message" data-message="{{ session('success') }}"></div>
@endif

@unless ($perfilProveedor)
    <div class="alert alert-warning">
        Necesitas tener un perfil de proveedor para recibir solicitudes.
    </div>
@else
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Solicitudes asignadas a tu perfil</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0 solicitudes-table">
                    <thead>
                        <tr>
                            <th>Solicitud</th>
                            <th>Cliente</th>
                            <th>Especialidad</th>
                            <th>Fecha solicitada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($solicitudes as $solicitud)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $solicitud->titulo }}</div>
                                    <small class="text-muted">{{ $solicitud->descripcion }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $solicitud->cliente?->name ?? 'Sin cliente' }}</div>
                                    <small class="text-muted">{{ $solicitud->cliente?->email }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $solicitud->especialidad?->nombre ?? 'Sin especialidad' }}</div>
                                    <small class="text-muted">
                                        {{ $solicitud->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro' }}
                                        -
                                        {{ $solicitud->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo' }}
                                    </small>
                                </td>
                                <td>
                                    @if ($solicitud->fecha_solicitada)
                                        <div>{{ $solicitud->fecha_solicitada->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $solicitud->hora_solicitada?->format('H:i') ?: 'Sin hora' }}</small>
                                    @else
                                        <span class="text-muted">Sin fecha</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                                    </span>
                                </td>
                                <td>
                                    @can('gestionar solicitudes proveedor')
                                        <div class="hstack gap-2">
                                            @if ($solicitud->estado === 'pendiente')
                                                <form method="POST" action="{{ route('proveedor.solicitudes.estado', $solicitud->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="aceptada">
                                                    <button type="submit" class="btn btn-sm btn-soft-success" title="Aceptar">
                                                        <i class="ri-check-line align-bottom"></i>
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('proveedor.solicitudes.estado', $solicitud->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="rechazada">
                                                    <input type="hidden" name="comentario" value="Solicitud rechazada por el proveedor">
                                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Rechazar">
                                                        <i class="ri-close-line align-bottom"></i>
                                                    </button>
                                                </form>
                                            @elseif ($solicitud->estado === 'aceptada')
                                                <form method="POST" action="{{ route('proveedor.solicitudes.estado', $solicitud->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="en_proceso">
                                                    <button type="submit" class="btn btn-sm btn-soft-info" title="Iniciar atencion">
                                                        <i class="ri-play-line align-bottom"></i>
                                                    </button>
                                                </form>
                                            @elseif ($solicitud->estado === 'en_proceso')
                                                <form method="POST" action="{{ route('proveedor.solicitudes.estado', $solicitud->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="finalizada">
                                                    <button type="submit" class="btn btn-sm btn-soft-primary" title="Finalizar">
                                                        <i class="ri-flag-line align-bottom"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Sin acciones</span>
                                            @endif
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aun no tienes solicitudes recibidas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
@endunless
@endsection

@push('scripts')
<script src="{{ asset('assets/js/solicitudes.js') }}"></script>
@endpush
