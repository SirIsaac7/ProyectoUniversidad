@extends('layouts.app')

@section('title', 'Detalle de indexación')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
@php
    $badge = match ($ejecucion->categoria_estado) {
        'completado' => 'success',
        'error' => 'danger',
        'ejecucion' => 'info',
        'pendiente' => 'warning',
        default => 'secondary',
    };
    $metricas = [
        'Total de proveedores' => $ejecucion->total_proveedores,
        'Proveedores procesados' => $ejecucion->proveedores_procesados,
        'Proveedores nuevos' => $ejecucion->proveedores_nuevos,
        'Reindexados' => $ejecucion->proveedores_reindexados,
        'Metadatos actualizados' => $ejecucion->metadatos_actualizados,
        'Sin modificaciones' => $ejecucion->proveedores_sin_modificaciones,
        'Eliminados del índice' => $ejecucion->proveedores_eliminados,
    ];
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <div>
                <h4 class="mb-1">Detalle de indexación #{{ $ejecucion->id }}</h4>
                <p class="text-muted mb-0">Información permanente registrada por Laravel.</p>
            </div>
            <a href="{{ route('motor-busqueda.index') }}" class="btn btn-light">
                <i class="ri-arrow-left-line align-bottom me-1"></i>Volver al panel
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div>
                <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} mb-2">{{ $ejecucion->estado }}</span>
                <h4 class="mb-1">{{ $ejecucion->fase_actual ?: 'Sin fase informada' }}</h4>
                <p class="text-muted mb-0">{{ $ejecucion->descripcion_fase ?: 'El motor no proporcionó una descripción de fase.' }}</p>
            </div>
            <div class="text-sm-end">
                <small class="text-muted d-block">Identificador FastAPI</small>
                <code>{{ $ejecucion->fastapi_identificador ?: 'No asignado' }}</code>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3"><div class="motor-detail-field"><small>Tipo</small><strong class="text-capitalize">{{ $ejecucion->tipo }}</strong></div></div>
            <div class="col-md-3"><div class="motor-detail-field"><small>Responsable</small><strong>{{ $ejecucion->tipo === 'automatica' ? 'Sistema' : ($ejecucion->usuario?->name ?? 'Usuario eliminado') }}</strong></div></div>
            <div class="col-md-3"><div class="motor-detail-field"><small>Inicio</small><strong>{{ $ejecucion->iniciado_en?->format('d/m/Y H:i:s') ?? '—' }}</strong></div></div>
            <div class="col-md-3"><div class="motor-detail-field"><small>Finalización</small><strong>{{ $ejecucion->finalizado_en?->format('d/m/Y H:i:s') ?? '—' }}</strong></div></div>
        </div>
    </div>
</div>

<section class="mb-4">
    <h5 class="mb-3">Resultados</h5>
    <div class="row g-3">
        @foreach ($metricas as $titulo => $valor)
            <div class="col-sm-6 col-xl-3">
                <article class="motor-metric-card">
                    <span>{{ $titulo }}</span>
                    <strong>{{ $valor ?? '—' }}</strong>
                </article>
            </div>
        @endforeach
    </div>
</section>

@if ($ejecucion->errores)
    <section class="card border-danger mb-4">
        <div class="card-header"><h5 class="card-title text-danger mb-0">Errores registrados</h5></div>
        <div class="card-body">
            <ul class="mb-0">
                @foreach ($ejecucion->errores as $error)
                    <li>{{ is_string($error) ? $error : json_encode($error, JSON_UNESCAPED_UNICODE) }}</li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

<section class="card">
    <div class="card-header"><h5 class="card-title mb-0">Historial de fases</h5></div>
    <div class="card-body">
        <div class="motor-timeline">
            @forelse ($ejecucion->fases ?? [] as $fase)
                @php $fase = is_array($fase) ? $fase : ['nombre' => (string) $fase]; @endphp
                <article class="motor-timeline-item">
                    <span class="motor-timeline-dot"><i class="ri-check-line"></i></span>
                    <div class="motor-timeline-content">
                        <h6 class="mb-1">{{ $fase['nombre'] ?? $fase['fase'] ?? 'Fase' }}</h6>
                        <p class="text-muted mb-2">{{ $fase['descripcion'] ?? $fase['detalle'] ?? 'Sin descripción adicional.' }}</p>
                        <div class="motor-timeline-meta">
                            <span>{{ $fase['estado'] ?? '' }}</span>
                            <span>{{ $fase['iniciado_en'] ?? $fase['fecha_inicio'] ?? '' }}</span>
                            <span>{{ $fase['finalizado_en'] ?? $fase['fecha_fin'] ?? '' }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-muted mb-0">El motor no registró fases para esta ejecución.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
