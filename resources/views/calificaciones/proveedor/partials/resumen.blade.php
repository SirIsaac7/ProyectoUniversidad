@php
@endphp

<div class="calificacion-provider-dashboard mb-3">
    <div class="row g-3 mb-3">
        <div class="col-xl col-md-6">
            <div class="card calificacion-metric-card h-100">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Promedio general</p>
                    <h2 class="mb-1">{{ number_format($resumenCalificaciones['promedio'], 1) }}</h2>
                    <div class="calificacion-stars fs-18">
                        @for ($estrella = 1; $estrella <= 5; $estrella++)
                            <i class="ri-star-{{ $estrella <= round($resumenCalificaciones['promedio']) ? 'fill' : 'line' }}"></i>
                        @endfor
                    </div>
                    <small class="text-muted">({{ $resumenCalificaciones['total'] }} calificaciones)</small>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card calificacion-metric-card h-100">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Total de calificaciones</p>
                    <h2 class="mb-1">{{ $resumenCalificaciones['total'] }}</h2>
                    <small class="text-muted">100% del total</small>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card calificacion-metric-card h-100">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Calificaciones positivas</p>
                    <h2 class="mb-1 text-success">{{ number_format($resumenCalificaciones['positivas']['porcentaje'], 1) }}%</h2>
                    <small class="text-muted">{{ $resumenCalificaciones['positivas']['cantidad'] }} calificaciones</small>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card calificacion-metric-card h-100">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Respuestas realizadas</p>
                    <h2 class="mb-1 text-primary">{{ $resumenCalificaciones['respuestas']['cantidad'] }}</h2>
                    <small class="text-muted">{{ number_format($resumenCalificaciones['respuestas']['porcentaje'], 1) }}% del total</small>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card calificacion-metric-card h-100">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Clientes unicos</p>
                    <h2 class="mb-1 text-info">{{ $resumenCalificaciones['clientes_unicos'] }}</h2>
                    <small class="text-muted">De {{ $resumenCalificaciones['total'] }} calificaciones</small>
                </div>
            </div>
        </div>
    </div>
</div>
