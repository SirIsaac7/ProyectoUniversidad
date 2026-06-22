<div class="card calificacion-summary-card mb-3">
    <div class="card-body">
        <div class="row g-0 align-items-center text-center">
            <div class="col-md-2 calificacion-summary-block">
                <p class="text-muted mb-1">Calificación promedio</p>
                <h2 class="mb-1">{{ number_format($resumenCalificaciones['promedio'], 1) }}</h2>
                <div class="calificacion-stars fs-18">★★★★★</div>
                <small class="text-muted">({{ $resumenCalificaciones['total'] }} calificaciones)</small>
            </div>

            <div class="col-md-2 calificacion-summary-block">
                <p class="text-muted mb-1">Total de reseñas</p>
                <h2 class="mb-1">{{ $resumenCalificaciones['total'] }}</h2>
                <small class="text-muted">Registradas</small>
            </div>

            @foreach ([5, 4, 3, 2] as $estrella)
                <div class="col-md-2 calificacion-summary-block">
                    <p class="mb-1 text-{{ $estrella >= 4 ? 'success' : 'warning' }}">{{ $estrella }} estrellas</p>
                    <h2 class="mb-1">{{ $resumenCalificaciones['conteos'][$estrella]['cantidad'] }}</h2>
                    <small class="text-muted">{{ $resumenCalificaciones['conteos'][$estrella]['porcentaje'] }}%</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
