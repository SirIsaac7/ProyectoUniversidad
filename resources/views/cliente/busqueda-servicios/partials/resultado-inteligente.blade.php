<div class="busqueda-ia-resultados">
    <div class="card busqueda-ia-summary mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-primary-subtle text-primary mb-2">
                        <i class="ri-sparkling-line align-bottom me-1"></i>
                        Resultado inteligente
                    </span>
                    <h5 class="mb-1">
                        {{ $clasificacion['tipo_dispositivo'] ? ucfirst($clasificacion['tipo_dispositivo']) : 'Servicio detectado' }}
                    </h5>
                    <p class="text-muted mb-1">
                        <strong>Marca:</strong>
                        @if (! empty($clasificacion['marca']))
                            {{ $clasificacion['marca'] }}
                        @elseif (($clasificacion['tipo_dispositivo'] ?? null) === 'laptops')
                            No detectada. La marca no siempre se infiere para laptops con la configuracion actual.
                        @else
                            No detectada en esta imagen.
                        @endif
                    </p>
                    <p class="text-muted mb-0">
                        Se priorizaron proveedores por coincidencia con especialidades, descripcion, cobertura y datos del sistema.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if (! empty($clasificacion['confianza']))
                        <span class="badge bg-success-subtle text-success">
                            Confianza: {{ $clasificacion['confianza'] }}%
                        </span>
                    @endif
                    <span class="badge bg-info-subtle text-info">
                        Modo:
                        @if (($clasificacion['modo_usado'] ?? 'cnn') === 'gemini')
                            PRO
                        @elseif (($clasificacion['modo_usado'] ?? 'cnn') === 'cnn_fallback')
                            CNN fallback
                        @else
                            CNN
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if ($proveedores->isNotEmpty())
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Proveedores sugeridos por IA</h5>
            <span class="badge bg-primary-subtle text-primary">{{ $proveedores->count() }} resultados</span>
        </div>

        <div class="proveedores-resultados-grid proveedores-resultados-grid--cards">
            @foreach ($proveedores as $proveedor)
                <div
                    class="card proveedor-search-card proveedor-search-card-horizontal proveedor-search-card--reveal busqueda-ia-card"
                    style="--card-delay: {{ $loop->index * 70 }}ms;"
                >
                    <div class="proveedor-search-personal-photo">
                        @if ($proveedor['foto_personal'])
                            <img src="{{ $proveedor['foto_personal'] }}" alt="{{ $proveedor['nombre_persona'] }}" referrerpolicy="no-referrer">
                        @else
                            <span>{{ mb_substr($proveedor['nombre_persona'], 0, 1) }}</span>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-1">
                            <div class="min-w-0">
                                <h5 class="mb-1 text-truncate">{{ $proveedor['nombre_persona'] }}</h5>
                                <p class="text-muted mb-0 text-truncate">{{ $proveedor['nombre_publico'] }}</p>
                            </div>
                            <span class="badge bg-success-subtle text-success flex-shrink-0">
                                {{ number_format($proveedor['score_ia'], 0) }}%
                            </span>
                        </div>

                        <div class="proveedor-search-stars mb-2">
                            @php($rating = $proveedor['rating_ia'] > 0 ? $proveedor['rating_ia'] : 4.5)
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= floor($rating) ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                            @endfor
                            <span>{{ number_format($rating, 1) }}</span>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 text-muted small mb-2">
                            <span>
                                <i class="ri-tools-line align-bottom me-1"></i>
                                {{ $proveedor['especialidad'] }}
                            </span>
                            <span>
                                <i class="ri-map-pin-line align-bottom me-1"></i>
                                {{ $proveedor['zona'] }}
                            </span>
                            @if ($proveedor['distancia_ia'])
                                <span>
                                    <i class="ri-route-line align-bottom me-1"></i>
                                    {{ $proveedor['distancia_ia'] }} km
                                </span>
                            @endif
                        </div>

                        @if (! empty($proveedor['razones_ia']))
                            <div class="busqueda-ia-reasons mb-3">
                                @foreach (array_slice($proveedor['razones_ia'], 0, 3) as $razon)
                                    <span>{{ $razon }}</span>
                                @endforeach
                            </div>
                        @endif

                        <button
                            type="button"
                            class="btn btn-soft-primary btn-lg w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#proveedorPerfilModal{{ $proveedor['id'] }}Ia"
                            data-proveedor-modal-tab="perfil"
                        >
                            <i class="ri-user-search-line align-bottom me-1"></i>
                            Ver perfil
                        </button>
                    </div>
                </div>

                @include('cliente.busqueda-servicios.partials.modal-proveedor', ['proveedor' => $proveedor, 'tiposAtencion' => $tiposAtencion, 'modalSufijo' => 'Ia'])
            @endforeach
        </div>
    @else
        <div class="card proveedores-resultados-empty">
            <div class="card-body text-center py-5">
                <div class="avatar-md mx-auto mb-3">
                    <span class="avatar-title rounded-circle bg-warning-subtle text-warning">
                        <i class="ri-search-eye-line fs-24"></i>
                    </span>
                </div>
                <h5 class="mb-2">No encontramos proveedores compatibles</h5>
                <p class="text-muted mb-0">La IA pudo clasificar el problema, pero no hay proveedores activos que coincidan en este momento.</p>
            </div>
        </div>
    @endif
</div>
