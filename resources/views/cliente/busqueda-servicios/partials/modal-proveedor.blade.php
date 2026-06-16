@php
    $tieneErrorSolicitud = $errors->any() && (string) old('perfil_proveedor_id') === (string) $proveedor['id'];
@endphp

<div class="modal fade proveedor-busqueda-modal" id="proveedorPerfilModal{{ $proveedor['id'] }}" data-proveedor-id="{{ $proveedor['id'] }}" tabindex="-1" aria-labelledby="proveedorPerfilModalLabel{{ $proveedor['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content proveedor-profile-modal {{ $tieneErrorSolicitud ? 'is-requesting' : '' }}" data-proveedor-profile-modal>
            <div class="proveedor-profile-view" data-proveedor-profile-view>
                <div class="proveedor-profile-hero">
                    @if ($proveedor['foto_portada'])
                        <img src="{{ asset($proveedor['foto_portada']) }}" alt="{{ $proveedor['nombre_publico'] }}">
                    @else
                        <div class="proveedor-profile-hero-empty">
                            <i class="ri-tools-line"></i>
                        </div>
                    @endif
                    <div class="proveedor-profile-hero-overlay"></div>
                    <button type="button" class="btn-close btn-close-white proveedor-profile-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                    <div class="proveedor-profile-hero-content">
                        <div class="proveedor-profile-avatar">
                            @if ($proveedor['foto_personal'])
                                <img src="{{ $proveedor['foto_personal'] }}" alt="{{ $proveedor['nombre_persona'] }}">
                            @else
                                <span>{{ mb_substr($proveedor['nombre_persona'], 0, 1) }}</span>
                            @endif
                        </div>

                        <div class="proveedor-profile-title">
                            <span class="badge bg-success-subtle text-success mb-2">
                                <i class="ri-checkbox-blank-circle-fill me-1"></i>
                                Disponible
                            </span>
                            <h3 id="proveedorPerfilModalLabel{{ $proveedor['id'] }}" class="text-white mb-1">
                                {{ $proveedor['nombre_persona'] }}
                                <i class="ri-verified-badge-fill text-primary fs-20 align-middle"></i>
                            </h3>
                            <p class="text-white-50 mb-2">{{ $proveedor['nombre_publico'] }}</p>
                            <div class="proveedor-search-stars">
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-half-fill"></i>
                                <span class="text-white">4.5</span>
                            </div>
                        </div>

                        <div class="proveedor-profile-metrics">
                            <div>
                                <i class="ri-calendar-line"></i>
                                <strong>{{ $proveedor['anios_experiencia'] }}</strong>
                                <span>Años exp.</span>
                            </div>
                            <div>
                                <i class="ri-tools-line"></i>
                                <strong>{{ $proveedor['especialidades']->count() }}</strong>
                                <span>Especialidades</span>
                            </div>
                        <div>
                            <i class="ri-map-pin-line"></i>
                            <strong>{{ $proveedor['zona'] }}</strong>
                            <span>Zona de servicio</span>
                        </div>
                    </div>

                    @can('crear mis solicitudes')
                        <div class="proveedor-profile-cta-card">
                            <h5>Solicitar servicio</h5>
                            <p>Abre el formulario completo para explicar lo que necesitas.</p>
                            <button class="btn btn-primary w-100 mb-2" type="button" data-proveedor-request-open>
                                <i class="ri-send-plane-line align-bottom me-1"></i>
                                Solicitar servicio
                            </button>
                            <div class="small text-success text-center">
                                <i class="ri-checkbox-blank-circle-fill me-1"></i>
                                Responde segun su disponibilidad
                            </div>
                        </div>
                    @endcan
                </div>
            </div>

            <div class="modal-body p-0">
                <div class="proveedor-profile-layout">
                        <div class="proveedor-profile-main">
                            <ul class="nav proveedor-profile-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#proveedorInformacion{{ $proveedor['id'] }}" type="button" role="tab">Informacion</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#proveedorEspecialidades{{ $proveedor['id'] }}" type="button" role="tab">Especialidades</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#proveedorHorarios{{ $proveedor['id'] }}" type="button" role="tab">Horarios</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#proveedorUbicacion{{ $proveedor['id'] }}" type="button" role="tab">Ubicacion</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#proveedorTrabajos{{ $proveedor['id'] }}" type="button" role="tab">Trabajos realizados</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#proveedorDocumentos{{ $proveedor['id'] }}" type="button" role="tab">Documentos</button>
                                </li>
                            </ul>

                            <div class="tab-content proveedor-profile-content">
                                <div class="tab-pane fade show active" id="proveedorInformacion{{ $proveedor['id'] }}" role="tabpanel">
                                        <section class="proveedor-profile-section proveedor-profile-section-info">
                                            <span class="proveedor-section-watermark"><i class="ri-user-star-line"></i></span>
                                            <h5>Perfil del proveedor</h5>
                                            <p class="text-muted mb-3">{{ $proveedor['descripcion'] }}</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-primary-subtle text-primary"><i class="ri-shield-check-line me-1"></i> Perfil verificado</span>
                                            <span class="badge bg-success-subtle text-success"><i class="ri-home-gear-line me-1"></i> Atencion {{ $proveedor['tipo_servicio'] }}</span>
                                            <span class="badge bg-info-subtle text-info"><i class="ri-map-pin-line me-1"></i> {{ $proveedor['zona'] }}</span>
                                            <span class="badge bg-warning-subtle text-warning"><i class="ri-star-line me-1"></i> Calificacion visible pronto</span>
                                        </div>
                                    </section>

                                        <section class="proveedor-profile-section proveedor-profile-section-info">
                                            <h5>Datos principales</h5>
                                        <div class="proveedor-modal-info mt-3">
                                            <div>
                                                <small class="text-muted d-block">Persona</small>
                                                <strong>{{ $proveedor['nombre_persona'] }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Nombre publico / negocio</small>
                                                <strong>{{ $proveedor['nombre_publico'] }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Rubro principal</small>
                                                <strong>{{ $proveedor['categoria'] }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Tipo de servicio principal</small>
                                                <strong>{{ $proveedor['tipo_servicio'] }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Especialidad principal</small>
                                                <strong>{{ $proveedor['especialidad'] }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Experiencia</small>
                                                <strong>{{ $proveedor['anios_experiencia'] }} años</strong>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="tab-pane fade" id="proveedorEspecialidades{{ $proveedor['id'] }}" role="tabpanel">
                                        <section class="proveedor-profile-section proveedor-profile-section-specialties">
                                            <span class="proveedor-section-watermark"><i class="ri-tools-line"></i></span>
                                            <h5>Especialidades</h5>
                                            <p class="text-muted">Estas son las areas concretas en las que el proveedor puede atender solicitudes.</p>
                                            <div class="row g-3 mt-1">
                                                @foreach ($proveedor['especialidades'] as $especialidad)
                                                    @php
                                                        $tonoEspecialidad = ['purple', 'green', 'blue'][$loop->index % 3];
                                                    @endphp
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="proveedor-especialidad-card is-{{ $tonoEspecialidad }}">
                                                            <div class="proveedor-especialidad-top">
                                                                <span class="proveedor-especialidad-icon">
                                                                    <i class="ri-tools-line"></i>
                                                                </span>
                                                                <div>
                                                                    <h4>{{ $especialidad['nombre'] }}</h4>
                                                                    @if ($especialidad['es_principal'])
                                                                        <span class="badge proveedor-especialidad-badge">Principal</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <p class="proveedor-especialidad-description">
                                                                {{ $especialidad['descripcion'] ?: 'Atencion relacionada con ' . $especialidad['tipo_servicio'] . ' dentro del rubro ' . $especialidad['rubro'] . '.' }}
                                                            </p>

                                                            <div class="proveedor-especialidad-list">
                                                                <div>
                                                                    <i class="ri-checkbox-circle-fill"></i>
                                                                    <span>Rubro: {{ $especialidad['rubro'] }}</span>
                                                                </div>
                                                                <div>
                                                                    <i class="ri-checkbox-circle-fill"></i>
                                                                    <span>Tipo: {{ $especialidad['tipo_servicio'] }}</span>
                                                                </div>
                                                                <div>
                                                                    <i class="ri-checkbox-circle-fill"></i>
                                                                    <span>{{ $especialidad['es_principal'] ? 'Especialidad principal' : 'Especialidad activa' }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="proveedor-especialidad-footer">
                                                                <i class="ri-customer-service-2-line"></i>
                                                                Disponible para solicitudes
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </section>
                                </div>

                                <div class="tab-pane fade" id="proveedorHorarios{{ $proveedor['id'] }}" role="tabpanel">
                                        <section class="proveedor-profile-section proveedor-profile-section-schedule">
                                            <span class="proveedor-section-watermark"><i class="ri-calendar-check-line"></i></span>
                                            <h5>Horarios de atencion</h5>
                                            <p class="text-muted">Consulta la disponibilidad registrada por el proveedor antes de enviar tu solicitud.</p>
                                            <div class="proveedor-horario-summary">
                                                <div>
                                                    <small>Estado actual</small>
                                                    <span class="proveedor-horario-status {{ $proveedor['resumen_horario']['disponible_ahora'] ? 'is-available' : 'is-unavailable' }}">
                                                        <i class="ri-checkbox-blank-circle-fill"></i>
                                                        {{ $proveedor['resumen_horario']['estado_texto'] }}
                                                    </span>
                                                    <p class="text-muted mb-0">{{ $proveedor['resumen_horario']['estado_ayuda'] }}</p>
                                                </div>
                                                <div>
                                                    <small>Proximo espacio libre</small>
                                                    <h5 class="mb-1"><i class="ri-calendar-line me-1"></i>{{ $proveedor['resumen_horario']['proximo_dia'] }}</h5>
                                                    <strong class="text-primary fs-18">{{ $proveedor['resumen_horario']['proximo_horario'] }}</strong>
                                                    <p class="text-muted mb-0">Horario registrado por el proveedor</p>
                                                </div>
                                                <div>
                                                    <small>Zona horaria</small>
                                                    <h5 class="mb-1"><i class="ri-global-line me-1"></i>GMT -4</h5>
                                                    <p class="text-muted mb-0">La Paz, Bolivia</p>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <h5 class="mb-1">Horario semanal</h5>
                                                <p class="text-muted">Consulta los horarios habituales de atencion.</p>
                                                <div class="proveedor-horario-list">
                                                    @forelse ($proveedor['horarios'] as $horario)
                                                        <div class="proveedor-horario-row">
                                                            <div>
                                                                <i class="{{ $horario['disponible'] ? 'ri-calendar-check-line text-success' : 'ri-calendar-close-line text-danger' }}"></i>
                                                                <strong>{{ $horario['dia'] }}</strong>
                                                            </div>
                                                            <span class="{{ $horario['disponible'] ? '' : 'text-danger fw-semibold' }}">
                                                                {{ $horario['disponible'] ? $horario['hora_inicio'] . ' - ' . $horario['hora_fin'] : 'No disponible' }}
                                                            </span>
                                                            <span class="badge {{ $horario['disponible'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                                {{ $horario['disponible'] ? 'Disponible' : 'No disponible' }}
                                                            </span>
                                                        </div>
                                                    @empty
                                                        <div class="proveedor-profile-empty">Este proveedor aun no registro horarios visibles.</div>
                                                    @endforelse
                                                </div>
                                            </div>

                                            <div class="alert alert-info bg-info-subtle border-info-subtle text-info mt-4 mb-4">
                                                <i class="ri-information-line me-1"></i>
                                                Los horarios pueden variar por feriados o carga de trabajo. Te recomendamos enviar tu solicitud con anticipacion.
                                            </div>

                                            <div>
                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                                    <div>
                                                        <h5 class="mb-1">Disponibilidad registrada</h5>
                                                        <p class="text-muted mb-0">Resumen visual segun los horarios cargados.</p>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-3 small">
                                                        <span><i class="ri-checkbox-blank-circle-fill text-success me-1"></i>Disponible</span>
                                                        <span><i class="ri-checkbox-blank-circle-fill text-danger me-1"></i>No disponible</span>
                                                    </div>
                                                </div>
                                                <div class="proveedor-horario-week-grid">
                                                    @forelse ($proveedor['horarios'] as $horario)
                                                        <div class="proveedor-horario-day-card {{ $horario['disponible'] ? 'is-available' : 'is-unavailable' }}">
                                                            <strong>{{ $horario['dia'] }}</strong>
                                                            <small>{{ $horario['hora_inicio'] }} - {{ $horario['hora_fin'] }}</small>
                                                            <span>
                                                                <i class="{{ $horario['disponible'] ? 'ri-check-line' : 'ri-close-line' }}"></i>
                                                            </span>
                                                            <p>{{ $horario['disponible'] ? 'Disponible' : 'No disponible' }}</p>
                                                        </div>
                                                    @empty
                                                        <div class="proveedor-profile-empty w-100">Sin disponibilidad visible.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                    </section>
                                </div>

                                <div class="tab-pane fade" id="proveedorUbicacion{{ $proveedor['id'] }}" role="tabpanel">
                                        <section class="proveedor-profile-section proveedor-profile-section-location">
                                            <span class="proveedor-section-watermark"><i class="ri-map-pin-line"></i></span>
                                            <h5>Ubicacion y cobertura</h5>
                                            <p class="text-muted">Consulta la ubicacion registrada y el alcance de atencion del proveedor.</p>
                                            <div class="proveedor-ubicacion-layout mt-3">
                                                <div class="mapa-lapaz-card proveedor-ubicacion-map-card">
                                                    <div class="mapa-lapaz-header">
                                                        <h5>Mapa del Municipio de La Paz</h5>
                                                        <p>Se resalta la zona si existe coincidencia con el mapa registrado.</p>
                                                    </div>
                                                    <div
                                                        id="mapaLaPazProveedor{{ $proveedor['id'] }}"
                                                        class="mapa-lapaz-canvas"
                                                        data-mapa-lapaz
                                                        data-zona="{{ $proveedor['zona'] }}"
                                                    ></div>
                                                    <div class="proveedor-ubicacion-map-legend">
                                                        <span></span>
                                                        Zona de cobertura
                                                    </div>
                                                </div>

                                                <div class="proveedor-ubicacion-cards">
                                                    <div class="proveedor-ubicacion-card">
                                                        <span class="proveedor-ubicacion-icon bg-primary-subtle text-primary">
                                                            <i class="ri-focus-3-line"></i>
                                                        </span>
                                                        <div>
                                                            <small>Zona / barrio</small>
                                                            <strong>{{ $proveedor['zona'] }}</strong>
                                                            <span class="badge bg-primary-subtle text-primary">Zona principal</span>
                                                        </div>
                                                    </div>

                                                    <div class="proveedor-ubicacion-card">
                                                        <span class="proveedor-ubicacion-icon bg-info-subtle text-info">
                                                            <i class="ri-map-pin-line"></i>
                                                        </span>
                                                        <div>
                                                            <small>Direccion registrada</small>
                                                            <strong>{{ $proveedor['direccion'] }}</strong>
                                                            <p class="text-muted mb-0">{{ $proveedor['zona'] }}, La Paz</p>
                                                        </div>
                                                    </div>

                                                    <div class="proveedor-ubicacion-card">
                                                        <span class="proveedor-ubicacion-icon bg-secondary-subtle text-secondary">
                                                            <i class="ri-radar-line"></i>
                                                        </span>
                                                        <div>
                                                            <small>Radio de cobertura</small>
                                                            <strong>{{ $proveedor['radio_cobertura_km'] ?? 1 }} km</strong>
                                                            <p class="text-muted mb-0">Alrededor de su ubicacion registrada</p>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                    </section>
                                </div>

                                <div class="tab-pane fade" id="proveedorTrabajos{{ $proveedor['id'] }}" role="tabpanel">
                                        <section class="proveedor-profile-section proveedor-profile-section-work">
                                            <span class="proveedor-section-watermark"><i class="ri-gallery-line"></i></span>
                                            <h5>Trabajos realizados</h5>
                                        <p class="text-muted">Evidencias publicadas por el proveedor en su portafolio.</p>
                                        <div class="row g-3 mt-1">
                                            @forelse ($proveedor['portafolio'] as $trabajo)
                                                <div class="col-md-6 col-xl-4">
                                                    <div class="proveedor-profile-work-card">
                                                        <div class="proveedor-modal-work-image">
                                                            @if ($trabajo['imagen'])
                                                                <img src="{{ asset($trabajo['imagen']) }}" alt="{{ $trabajo['titulo'] }}">
                                                            @else
                                                                <i class="ri-image-line"></i>
                                                            @endif
                                                            <span class="proveedor-cover-badge badge bg-warning-subtle text-warning">{{ $trabajo['fecha_trabajo'] ?? 'Trabajo' }}</span>
                                                        </div>
                                                        <div class="p-3">
                                                            <h6 class="mb-1">{{ $trabajo['titulo'] }}</h6>
                                                            <p class="text-muted small mb-0">{{ $trabajo['descripcion'] }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="proveedor-profile-empty">Este proveedor aun no publico trabajos visibles.</div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </section>
                                </div>

                                <div class="tab-pane fade" id="proveedorDocumentos{{ $proveedor['id'] }}" role="tabpanel">
                                        <section class="proveedor-profile-section proveedor-profile-section-documents">
                                            <span class="proveedor-section-watermark"><i class="ri-file-shield-line"></i></span>
                                            <h5>Documentos aprobados</h5>
                                        <p class="text-muted">Documentos visibles para respaldar la confianza del proveedor.</p>
                                        <div class="row g-3 mt-1">
                                            @forelse ($proveedor['documentos'] as $documento)
                                                <div class="col-md-6">
                                                    <div class="proveedor-profile-detail-card">
                                                        <span class="proveedor-sector-icon bg-success-subtle text-success">
                                                            <i class="ri-file-shield-line"></i>
                                                        </span>
                                                        <div>
                                                            <h6 class="mb-1">{{ $documento['nombre'] }}</h6>
                                                            @if ($documento['descripcion'])
                                                                <p class="text-muted small mb-2">{{ $documento['descripcion'] }}</p>
                                                            @endif
                                                            <span class="badge bg-success-subtle text-success">Aprobado</span>
                                                            @if ($documento['obligatorio'])
                                                                <span class="badge bg-warning-subtle text-warning">Obligatorio</span>
                                                            @endif
                                                            @if ($documento['fecha_revision'])
                                                                <span class="badge bg-info-subtle text-info">Rev. {{ $documento['fecha_revision'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="proveedor-profile-empty">Este proveedor aun no tiene documentos aprobados visibles.</div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </section>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            </div>

            @can('crear mis solicitudes')
                <div class="proveedor-request-view" data-proveedor-request-view>
                    <div class="proveedor-request-header">
                        <button type="button" class="btn btn-soft-secondary" data-proveedor-request-back>
                            <i class="ri-arrow-left-line align-bottom me-1"></i>
                            Volver al perfil
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="proveedor-request-body">
                        <div class="proveedor-request-aside">
                            <div class="proveedor-profile-avatar mx-auto mb-3">
                                @if ($proveedor['foto_personal'])
                                    <img src="{{ $proveedor['foto_personal'] }}" alt="{{ $proveedor['nombre_persona'] }}">
                                @else
                                    <span>{{ mb_substr($proveedor['nombre_persona'], 0, 1) }}</span>
                                @endif
                            </div>
                            <h4>{{ $proveedor['nombre_persona'] }}</h4>
                            <p class="text-muted">{{ $proveedor['nombre_publico'] }}</p>
                            <div class="d-grid gap-2">
                                <span class="badge bg-primary-subtle text-primary">{{ $proveedor['especialidad'] }}</span>
                                <span class="badge bg-info-subtle text-info">{{ $proveedor['zona'] }}</span>
                                <span class="badge bg-success-subtle text-success">{{ $proveedor['anios_experiencia'] }} años de experiencia</span>
                            </div>
                        </div>

                        <form class="proveedor-request-form proveedor-request-wizard needs-validation" novalidate method="POST" action="{{ route('cliente.solicitudes.store') }}" data-solicitud-wizard>
                            @csrf
                            <input type="hidden" name="origen" value="busqueda_servicios">
                            <input type="hidden" name="perfil_proveedor_id" value="{{ $proveedor['id'] }}">
                            <input type="hidden" name="titulo" value="{{ $tieneErrorSolicitud ? old('titulo') : 'Solicitud de ' . $proveedor['especialidad'] }}" data-solicitud-titulo>
                            <input type="hidden" name="latitud" value="{{ $tieneErrorSolicitud ? old('latitud') : '' }}">
                            <input type="hidden" name="longitud" value="{{ $tieneErrorSolicitud ? old('longitud') : '' }}">

                            <div class="mb-4">
                                <span class="badge bg-primary-subtle text-primary mb-2">
                                    <i class="ri-file-add-line me-1"></i>
                                    Nueva solicitud
                                </span>
                                <h4 class="mb-1">Cuentale al proveedor que necesitas</h4>
                                <p class="text-muted mb-0">Completa los pasos con la informacion necesaria para que pueda evaluar tu solicitud.</p>
                            </div>

                            @if ($tieneErrorSolicitud)
                                <div class="alert alert-danger bg-danger-subtle border-danger-subtle text-danger">
                                    Revisa los campos marcados antes de enviar la solicitud.
                                </div>
                            @endif

                            <div class="solicitud-wizard-steps" role="tablist">
                                <button type="button" class="solicitud-wizard-step is-active" data-wizard-step-button="0">
                                    <span>1</span>
                                    <strong>Servicio</strong>
                                </button>
                                <button type="button" class="solicitud-wizard-step" data-wizard-step-button="1">
                                    <span>2</span>
                                    <strong>Ubicacion</strong>
                                </button>
                                <button type="button" class="solicitud-wizard-step" data-wizard-step-button="2">
                                    <span>3</span>
                                    <strong>Agenda</strong>
                                </button>
                                <button type="button" class="solicitud-wizard-step" data-wizard-step-button="3">
                                    <span>4</span>
                                    <strong>Revision</strong>
                                </button>
                            </div>

                            <div class="solicitud-wizard-panels">
                                <section class="solicitud-wizard-panel is-active" data-wizard-panel="0">
                                    <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Especialidad <span class="text-danger">*</span></label>
                                            <select name="especialidad_id" class="form-select @if ($tieneErrorSolicitud) @error('especialidad_id') is-invalid @enderror @endif" required data-solicitud-especialidad>
                                        @foreach ($proveedor['especialidades'] as $especialidad)
                                            <option value="{{ $especialidad['id'] }}" data-titulo="Solicitud de {{ $especialidad['nombre'] }}" @selected(old('especialidad_id', $proveedor['especialidad_id']) == $especialidad['id'])>
                                                {{ $especialidad['rubro'] }} - {{ $especialidad['nombre'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                            @if ($tieneErrorSolicitud)
                                                @error('especialidad_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <div class="invalid-feedback">Por favor selecciona la especialidad que necesitas.</div>
                                                @enderror
                                            @else
                                                <div class="invalid-feedback">Por favor selecciona la especialidad que necesitas.</div>
                                            @endif
                                            <div class="valid-feedback">Especialidad seleccionada correctamente.</div>
                                        </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tipo de atencion <span class="text-danger">*</span></label>
                                    <select name="tipo_atencion" class="form-select @if ($tieneErrorSolicitud) @error('tipo_atencion') is-invalid @enderror @endif" required data-solicitud-tipo-atencion>
                                        @foreach ($tiposAtencion as $valor => $texto)
                                            <option value="{{ $valor }}" @selected(old('tipo_atencion', 'mixto') === $valor)>{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                    <div class="solicitud-help-badge mt-2" data-solicitud-atencion-ayuda></div>
                                            @if ($tieneErrorSolicitud)
                                                @error('tipo_atencion')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                                                @enderror
                                            @else
                                                <div class="invalid-feedback">Por favor selecciona el tipo de atencion.</div>
                                            @endif
                                            <div class="valid-feedback">Tipo de atencion seleccionado.</div>
                                        </div>

                                <div class="col-12">
                                    <label class="form-label">Descripcion del problema <span class="text-danger">*</span></label>
                                    <textarea name="descripcion" rows="6" class="form-control @if ($tieneErrorSolicitud) @error('descripcion') is-invalid @enderror @endif" maxlength="3000" placeholder="Describe que ocurre, desde cuando pasa y que necesitas que revise el proveedor." required>{{ $tieneErrorSolicitud ? old('descripcion') : '' }}</textarea>
                                    <div class="form-text">Mientras mas claro seas, mejor podra responder el proveedor.</div>
                                            @if ($tieneErrorSolicitud)
                                                @error('descripcion')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <div class="invalid-feedback">Por favor describe el problema o servicio que necesitas.</div>
                                                @enderror
                                            @else
                                                <div class="invalid-feedback">Por favor describe el problema o servicio que necesitas.</div>
                                            @endif
                                            <div class="valid-feedback">Descripcion suficiente para enviar.</div>
                                        </div>
                                    </div>
                                </section>

                                <section class="solicitud-wizard-panel" data-wizard-panel="1">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Zona</label>
                                            <select name="zona" class="form-select @if ($tieneErrorSolicitud) @error('zona') is-invalid @enderror @endif" data-zonas-lapaz-select data-selected="{{ $tieneErrorSolicitud ? old('zona') : '' }}">
                                                <option value="">Selecciona una zona</option>
                                                @if ($tieneErrorSolicitud && old('zona'))
                                                    <option value="{{ old('zona') }}" selected>{{ old('zona') }}</option>
                                                @endif
                                            </select>
                                            <div class="form-text">Las zonas se cargan desde el GeoJSON del Municipio de La Paz.</div>
                                            @if ($tieneErrorSolicitud)
                                                @error('zona')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Direccion</label>
                                            <input type="text" name="direccion" class="form-control @if ($tieneErrorSolicitud) @error('direccion') is-invalid @enderror @endif" value="{{ $tieneErrorSolicitud ? old('direccion') : '' }}" maxlength="255" placeholder="Ej: Av. 6 de Agosto, edificio, referencia">
                                            @if ($tieneErrorSolicitud)
                                                @error('direccion')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>

                                    <div class="alert alert-info bg-info-subtle border-info-subtle text-info mt-3 mb-0">
                                        <i class="ri-map-pin-line me-1"></i>
                                        Si eliges atencion a domicilio, agrega una direccion clara para que el proveedor pueda evaluar la distancia.
                                    </div>
                                </section>

                                <section class="solicitud-wizard-panel" data-wizard-panel="2">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha solicitada</label>
                                            <input type="date" name="fecha_solicitada" class="form-control @if ($tieneErrorSolicitud) @error('fecha_solicitada') is-invalid @enderror @endif" value="{{ $tieneErrorSolicitud ? old('fecha_solicitada') : '' }}" min="{{ now()->toDateString() }}">
                                            @if ($tieneErrorSolicitud)
                                                @error('fecha_solicitada')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <div class="invalid-feedback">La fecha solicitada no puede ser anterior a hoy.</div>
                                                @enderror
                                            @else
                                                <div class="invalid-feedback">La fecha solicitada no puede ser anterior a hoy.</div>
                                            @endif
                                            <div class="valid-feedback">Fecha disponible para solicitar.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Hora solicitada</label>
                                            <input type="time" name="hora_solicitada" class="form-control @if ($tieneErrorSolicitud) @error('hora_solicitada') is-invalid @enderror @endif" value="{{ $tieneErrorSolicitud ? old('hora_solicitada') : '' }}">
                                            @if ($tieneErrorSolicitud)
                                                @error('hora_solicitada')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Observaciones</label>
                                            <textarea name="observaciones" rows="4" class="form-control @if ($tieneErrorSolicitud) @error('observaciones') is-invalid @enderror @endif" maxlength="1000" placeholder="Agrega horarios alternativos, referencias o detalles adicionales.">{{ $tieneErrorSolicitud ? old('observaciones') : '' }}</textarea>
                                            @if ($tieneErrorSolicitud)
                                                @error('observaciones')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>
                                </section>

                                <section class="solicitud-wizard-panel" data-wizard-panel="3">
                                    <div class="solicitud-review-card">
                                        <span class="avatar-md">
                                            <span class="avatar-title rounded-circle bg-success-subtle text-success">
                                                <i class="ri-checkbox-circle-line fs-24"></i>
                                            </span>
                                        </span>
                                        <div>
                                            <h5 class="mb-1">Listo para enviar</h5>
                                            <p class="text-muted mb-0">Revisa que la descripcion, zona y horario sean correctos. El proveedor recibira la solicitud como pendiente.</p>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4">
                                <button type="button" class="btn btn-soft-secondary" data-proveedor-request-back>Cancelar</button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light" data-wizard-prev disabled>
                                        <i class="ri-arrow-left-line align-bottom me-1"></i>
                                        Anterior
                                    </button>
                                    <button type="button" class="btn btn-primary" data-wizard-next>
                                        Siguiente
                                        <i class="ri-arrow-right-line align-bottom ms-1"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary d-none" data-wizard-submit>
                                        <i class="ri-send-plane-line align-bottom me-1"></i>
                                        Enviar solicitud
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
