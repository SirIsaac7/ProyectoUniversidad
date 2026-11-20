@extends('layouts.app')

@section('title', 'Historial de indexaciones')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div>
    <div class="page-title-box bg-transparent">
        <h4 class="mb-1">Historial de indexaciones</h4>
        <p class="text-muted mb-0">Consulta las ejecuciones manuales y automáticas registradas.</p>
    </div>

    <section class="card" aria-labelledby="historialIndexacionesTitulo">
        <div class="card-header"><h5 class="card-title mb-0" id="historialIndexacionesTitulo">Ejecuciones registradas</h5></div>
        <div class="card-body">
            <form method="GET" action="{{ route('motor-busqueda.historial.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label" for="historialDesde">Desde</label>
                    <input type="date" class="form-control" id="historialDesde" name="fecha_desde" value="{{ $filtrosHistorial['fecha_desde'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="historialHasta">Hasta</label>
                    <input type="date" class="form-control @error('fecha_hasta') is-invalid @enderror" id="historialHasta" name="fecha_hasta" value="{{ $filtrosHistorial['fecha_hasta'] ?? '' }}">
                    @error('fecha_hasta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="historialTipo">Tipo</label>
                    <select class="form-select" id="historialTipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="manual" @selected(($filtrosHistorial['tipo'] ?? '') === 'manual')>Manual</option>
                        <option value="automatica" @selected(($filtrosHistorial['tipo'] ?? '') === 'automatica')>Automática</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="historialEstado">Estado</label>
                    <select class="form-select" id="historialEstado" name="estado">
                        <option value="">Todos</option>
                        @foreach (['pendiente' => 'Pendiente', 'ejecucion' => 'En ejecución', 'completado' => 'Completado', 'error' => 'Error', 'desconocido' => 'Desconocido'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(($filtrosHistorial['estado'] ?? '') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" title="Filtrar"><i class="ri-filter-3-line"></i></button>
                    <a href="{{ route('motor-busqueda.historial.index') }}" class="btn btn-light" title="Limpiar filtros"><i class="ri-refresh-line"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th><th>Tipo</th><th>Responsable</th><th>Estado</th>
                            <th>Duración</th><th>Procesados</th><th>Resultado</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historial as $ejecucion)
                            @php
                                $duracion = $ejecucion->duracion_segundos;
                                $badge = match ($ejecucion->categoria_estado) {
                                    'completado' => 'success',
                                    'error' => 'danger',
                                    'ejecucion' => 'info',
                                    'pendiente' => 'warning',
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td>{{ $ejecucion->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-primary-subtle text-primary text-capitalize">{{ $ejecucion->tipo }}</span></td>
                                <td>{{ $ejecucion->tipo === 'automatica' ? 'Sistema' : ($ejecucion->usuario?->name ?? 'Usuario eliminado') }}</td>
                                <td><span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ $ejecucion->estado }}</span></td>
                                <td>{{ $duracion !== null ? sprintf('%02d:%02d:%02d', intdiv($duracion, 3600), intdiv($duracion % 3600, 60), $duracion % 60) : '—' }}</td>
                                <td>{{ $ejecucion->proveedores_procesados ?? 0 }} / {{ $ejecucion->total_proveedores ?? '—' }}</td>
                                <td>{{ $ejecucion->categoria_estado === 'completado' ? 'Correcto' : ($ejecucion->categoria_estado === 'error' ? 'Con errores' : 'En proceso') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('motor-busqueda.historial.show', $ejecucion) }}" class="btn btn-sm btn-soft-primary" title="Ver detalle"><i class="ri-eye-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">Todavía no existen indexaciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($historial->hasPages())
                <div class="mt-3">{{ $historial->links() }}</div>
            @endif
        </div>
    </section>
</div>
@endsection
