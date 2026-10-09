@if (count($proveedores))
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <span class="badge bg-primary-subtle text-primary mb-2">
                <i class="ri-radar-line align-bottom me-1"></i>
                Busqueda inteligente
            </span>
            <h5 class="mb-0">Tecnicos recomendados para ti</h5>
        </div>
        <span class="badge bg-success-subtle text-success">{{ count($proveedores) }} {{ count($proveedores) === 1 ? 'resultado' : 'resultados' }}</span>
    </div>

    <div class="proveedores-resultados-grid proveedores-resultados-grid--cards busqueda-semantica-grid">
        @foreach ($proveedores as $proveedor)
            @php
                $modalKey = $proveedor['id'] . 'Semantica';
                $disponible = $proveedor['resumen_horario']['disponible_ahora'] ?? false;
            @endphp
            <article class="card proveedor-search-card proveedor-search-card-horizontal proveedor-search-card--reveal" style="--card-delay: {{ $loop->index * 70 }}ms;">
                <div class="proveedor-search-personal-photo">
                    @if ($proveedor['foto_personal'])
                        <img src="{{ $proveedor['foto_personal'] }}" alt="{{ $proveedor['nombre_persona'] }}">
                    @else
                        <span>{{ mb_substr($proveedor['nombre_persona'], 0, 1) }}</span>
                    @endif
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <div class="min-w-0">
                            <h5 class="mb-1 text-truncate">{{ $proveedor['nombre_publico'] }}</h5>
                            <p class="text-muted mb-0 text-truncate">{{ $proveedor['nombre_persona'] }}</p>
                        </div>
                        <span class="badge bg-success-subtle text-success mt-2">
                            <i class="ri-verified-badge-line me-1"></i>Verificado
                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary">{{ $proveedor['especialidad'] }}</span>
                        <span class="badge {{ $disponible ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                            {{ $disponible ? 'Disponible ahora' : 'Agenda disponible' }}
                        </span>
                    </div>

                    <div class="busqueda-semantica-meta mb-3">
                        <span class="busqueda-semantica-rating">
                            <i class="ri-star-fill text-warning"></i>
                            {{ $proveedor['calificacion'] ?? 'Sin calificaciones' }}
                            @if ($proveedor['total_calificaciones'])
                                <small>({{ $proveedor['total_calificaciones'] }})</small>
                            @endif
                        </span>
                        <span><i class="ri-briefcase-4-line"></i>{{ $proveedor['anios_experiencia'] }} AÑOS de experiencia</span>
                        @if ($proveedor['servicios_completados'] !== null)
                            <span><i class="ri-checkbox-circle-line"></i>{{ $proveedor['servicios_completados'] }} servicios completados</span>
                        @endif
                    </div>

                    @if ($proveedor['modalidades_atencion'])
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($proveedor['modalidades_atencion'] as $modalidad)
                                <span class="busqueda-semantica-modalidad"><i class="ri-checkbox-circle-line"></i>{{ $modalidad }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($proveedor['explicacion'])
                        <div class="busqueda-semantica-explicacion mb-3">
                            <strong><i class="ri-lightbulb-flash-line"></i> ¿Por qué aparece recomendado?</strong>
                            <ul>
                                @foreach ($proveedor['explicacion'] as $motivo)
                                    <li>{{ $motivo }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button
                        type="button"
                        class="btn btn-soft-primary w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#proveedorPerfilModal{{ $modalKey }}"
                        data-proveedor-modal-tab="perfil"
                    >
                        <i class="ri-user-search-line align-bottom me-1"></i>
                        Ver perfil
                    </button>
                </div>
            </article>

            @include('cliente.busqueda-servicios.partials.modal-proveedor', [
                'proveedor' => $proveedor,
                'tiposAtencion' => $tiposAtencion,
                'modalSufijo' => 'Semantica',
            ])
        @endforeach
    </div>
@else
    <div class="card proveedores-resultados-empty">
        <div class="card-body text-center py-5">
            <span class="avatar-md mx-auto mb-3">
                <span class="avatar-title rounded-circle bg-warning-subtle text-warning">
                    <i class="ri-search-eye-line fs-24"></i>
                </span>
            </span>
            <h5 class="mb-2">No encontramos tecnicos para esta consulta</h5>
            <p class="text-muted mb-0">Prueba describiendo el equipo, la falla y el servicio que necesitas con otras palabras.</p>
        </div>
    </div>
@endif
