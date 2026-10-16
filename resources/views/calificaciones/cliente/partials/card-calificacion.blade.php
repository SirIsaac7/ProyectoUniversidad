<div class="calificacion-review-card cliente-calificacion-card" id="clienteCalificacion{{ $calificacion->id }}">
    <button
        type="button"
        class="cliente-calificacion-summary js-cliente-calificacion-toggle"
        data-target="clienteCalificacionDetalle{{ $calificacion->id }}"
    >
        <span class="avatar-md flex-shrink-0">
            @if ($calificacion->cita?->solicitud?->perfilProveedor?->user?->avatar_url)
                <img src="{{ $calificacion->cita->solicitud->perfilProveedor->user->avatar_url }}" alt="Proveedor" class="rounded-circle img-fluid" referrerpolicy="no-referrer">
            @else
                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                    {{ $calificacion->cita?->solicitud?->perfilProveedor?->user?->inicial ?? 'P' }}
                </span>
            @endif
        </span>

        <span class="flex-grow-1 text-start">
            <span class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <span>
                    <span class="d-block fw-semibold fs-16">{{ $calificacion->cita?->solicitud?->perfilProveedor?->nombre_publico ?? 'Proveedor no disponible' }}</span>
                    <small class="text-muted">
                        Cita del {{ $calificacion->cita?->fecha_cita?->format('d/m/Y') ?? 'Sin fecha' }}
                        -
                        {{ $calificacion->cita?->solicitud?->especialidad?->nombre ?? 'Sin especialidad' }}
                    </small>
                </span>
                <span class="badge bg-{{ $calificacion->estado === 'visible' ? 'success' : 'warning' }}-subtle text-{{ $calificacion->estado === 'visible' ? 'success' : 'warning' }}">
                    {{ ucfirst(str_replace('_', ' ', $calificacion->estado)) }}
                </span>
            </span>

            <span class="d-flex align-items-center gap-2">
                <span class="calificacion-stars-icon">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="{{ $i <= $calificacion->puntuacion ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                    @endfor
                </span>
                <strong>{{ number_format($calificacion->puntuacion, 1) }}</strong>
            </span>
        </span>

        <i class="ri-arrow-down-s-line fs-22 cliente-calificacion-arrow"></i>
    </button>

    <div class="cliente-calificacion-detail d-none" id="clienteCalificacionDetalle{{ $calificacion->id }}">
        <p class="mb-3">{{ $calificacion->comentario ?: 'Sin comentario registrado.' }}</p>

        @if ($calificacion->detalles->isNotEmpty())
            <div class="calificacion-criteria-compact mb-3">
                @foreach ($calificacion->detalles as $detalle)
                    <div class="calificacion-criteria-chip">
                        <span>{{ $detalle->aspecto?->nombre ?? 'Aspecto' }}</span>
                        <strong class="calificacion-stars-icon calificacion-stars-mini">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $detalle->puntuacion ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                            @endfor
                        </strong>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($calificacion->respuesta)
            <div class="calificacion-response-box">
                <span class="badge bg-info-subtle text-info mb-2">Respuesta del proveedor</span>
                <p class="mb-0">{{ $calificacion->respuesta->respuesta }}</p>
            </div>
        @endif
    </div>
</div>
