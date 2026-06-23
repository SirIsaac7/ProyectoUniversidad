<div class="card h-100 inicio-proveedores-card">
    <div class="card-header align-items-center d-flex">
        <div class="flex-grow-1">
            <h5 class="card-title mb-1">Proveedores atendidos</h5>
            <p class="text-muted mb-0">Profesionales con citas completadas contigo.</p>
        </div>
        <span class="badge bg-primary-subtle text-primary">
            {{ $proveedores->count() }}
        </span>
    </div>

    <div class="card-body inicio-proveedores-scroll">
        @forelse ($proveedores as $proveedor)
            <div class="inicio-proveedor-item">
                <div class="inicio-proveedor-avatar">
                    @if ($proveedor['avatar'])
                        <img src="{{ $proveedor['avatar'] }}" alt="{{ $proveedor['nombre'] }}">
                    @else
                        <span>{{ $proveedor['inicial'] }}</span>
                    @endif
                </div>

                <div class="inicio-proveedor-info">
                    <h6 class="mb-1">{{ $proveedor['nombre'] }}</h6>
                    <p class="text-muted mb-0">
                        {{ $proveedor['citasCliente'] }}
                        {{ $proveedor['citasCliente'] === 1 ? 'cita completada' : 'citas completadas' }}
                    </p>
                </div>

                <div
                    class="inicio-proveedor-rating-chart"
                    data-inicio-proveedor-rating-chart
                    data-chart='@json($proveedor['serie'])'
                ></div>

                <div class="inicio-proveedor-rating">
                    <strong>{{ number_format($proveedor['promedio'], 1) }}</strong>
                    <span class="text-warning">
                        <i class="ri-star-fill"></i>
                    </span>
                    <small class="text-muted d-block">
                        {{ $proveedor['totalCalificaciones'] }} reseñas
                    </small>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <div class="avatar-sm mx-auto mb-3">
                    <span class="avatar-title rounded-circle bg-light text-muted">
                        <i class="ri-user-search-line fs-20"></i>
                    </span>
                </div>
                <h6 class="mb-1">Aun no tienes proveedores atendidos</h6>
                <p class="text-muted mb-0">Cuando completes una cita, aparecera aqui el resumen del proveedor.</p>
            </div>
        @endforelse
    </div>
</div>
