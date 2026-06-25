<div class="calificacion-review-card proveedor-calificacion-card" id="calificacion-{{ $calificacion->id }}">
    <button
        type="button"
        class="proveedor-calificacion-summary js-proveedor-calificacion-toggle"
        data-target="proveedorCalificacionDetalle{{ $calificacion->id }}"
    >
        <span class="avatar-md flex-shrink-0">
            @if ($calificacion->cita?->solicitud?->cliente?->avatar_url)
                <img src="{{ $calificacion->cita->solicitud->cliente->avatar_url }}" alt="Cliente" class="rounded-circle img-fluid" referrerpolicy="no-referrer">
            @else
                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                    {{ $calificacion->cita?->solicitud?->cliente?->inicial ?? 'C' }}
                </span>
            @endif
        </span>

        <span class="flex-grow-1 text-start">
            <span class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <span>
                    <span class="d-block fw-semibold fs-16">{{ $calificacion->cita?->solicitud?->cliente?->name ?? 'Cliente no disponible' }}</span>
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

        <i class="ri-arrow-down-s-line fs-22 proveedor-calificacion-arrow"></i>
    </button>

    <div class="proveedor-calificacion-detail d-none" id="proveedorCalificacionDetalle{{ $calificacion->id }}">
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
            <div class="calificacion-response-box mb-3">
                <span class="badge bg-info-subtle text-info mb-2">Respondida</span>
                <p class="mb-0">{{ $calificacion->respuesta->respuesta }}</p>
            </div>

            @can('update', $calificacion->respuesta)
                <form method="POST" action="{{ route('proveedor.calificaciones.respuestas.update', $calificacion->respuesta->id) }}" class="calificacion-response-form">
                    @csrf
                    @method('PUT')
                    <textarea name="respuesta" rows="3" class="form-control mb-2" required>{{ old('respuesta', $calificacion->respuesta->respuesta) }}</textarea>
                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Actualizar respuesta
                        </button>
                    </div>
                </form>
            @else
                <div class="alert alert-info mb-0">
                    Esta respuesta ya no puede editarse.
                </div>
            @endcan
        @else
            @can('responder mis calificaciones proveedor')
                <form method="POST" action="{{ route('proveedor.calificaciones.respuestas.store') }}" class="calificacion-response-form">
                    @csrf
                    <input type="hidden" name="calificacion_id" value="{{ $calificacion->id }}">
                    <label class="form-label">Responder al cliente</label>
                    <textarea name="respuesta" rows="3" class="form-control @error('respuesta') is-invalid @enderror" placeholder="Escribe una respuesta profesional y amable." required>{{ old('respuesta') }}</textarea>
                    @error('respuesta')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ri-reply-line align-bottom me-1"></i>
                            Responder
                        </button>
                    </div>
                </form>
            @endcan
        @endif
    </div>
</div>
