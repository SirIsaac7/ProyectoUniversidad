@php
    $conteos = $resumenCalificaciones['conteos'];
    $distribucionSeries = collect([5, 4, 3, 2, 1])
        ->map(fn ($estrella) => $conteos[$estrella]['cantidad'] ?? 0)
        ->values();
    $distribucionLabels = collect([5, 4, 3, 2, 1])
        ->map(fn ($estrella) => $estrella . ' ' . ($estrella === 1 ? 'estrella' : 'estrellas'))
        ->values();
    $aspectos = $resumenCalificaciones['aspectos'] ?? ['labels' => [], 'series' => [], 'total' => 0];
@endphp

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card calificacion-chart-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribucion de calificaciones</h5>
            </div>
            <div class="card-body">
                <div
                    id="proveedorDistribucionCalificacionesChart"
                    class="calificacion-provider-chart"
                    data-series='@json($distribucionSeries->all())'
                    data-labels='@json($distribucionLabels->all())'
                    data-total="{{ $resumenCalificaciones['total'] }}"
                ></div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card calificacion-chart-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Calificaciones por aspecto</h5>
            </div>
            <div class="card-body">
                @if (($aspectos['total'] ?? 0) > 0)
                    <div
                        id="proveedorAspectosCalificacionesChart"
                        class="calificacion-provider-donut"
                        data-series='@json($aspectos['series'])'
                        data-labels='@json($aspectos['labels'])'
                        data-total="{{ $aspectos['total'] }}"
                    ></div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-3">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                <i class="ri-pie-chart-2-line fs-24"></i>
                            </span>
                        </div>
                        <h5 class="mb-1">Sin aspectos calificados</h5>
                        <p class="text-muted mb-0">Cuando existan calificaciones con aspectos, se mostrara la grafica.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
