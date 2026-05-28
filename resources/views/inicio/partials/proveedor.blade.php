@php
    $perfil = $data['perfil'];
    $fotoPortada = $perfil->foto_portada ? asset($perfil->foto_portada) : null;

    $estadoClass = match ($perfil->estado_verificacion) {
        'aprobado' => 'success',
        'rechazado' => 'danger',
        default => 'warning',
    };

    $acciones = [
        [
            'permiso' => 'actualizar perfil proveedor',
            'titulo' => 'Editar perfil',
            'texto' => 'Actualiza tu informacion principal',
            'icono' => 'ri-user-heart-line',
            'color' => 'primary',
            'url' => route('mi-perfil-proveedor.index'),
        ],
        [
            'permiso' => 'gestionar horarios proveedor',
            'titulo' => 'Actualizar horarios',
            'texto' => 'Gestiona tu disponibilidad',
            'icono' => 'ri-calendar-schedule-line',
            'color' => 'info',
            'url' => route('mi-perfil-proveedor.horarios.index'),
        ],
        [
            'permiso' => 'gestionar ubicacion proveedor',
            'titulo' => 'Actualizar ubicacion',
            'texto' => 'Edita tu zona de atencion',
            'icono' => 'ri-map-pin-line',
            'color' => 'success',
            'url' => route('mi-perfil-proveedor.ubicacion.index'),
        ],
        [
            'permiso' => 'gestionar portafolio proveedor',
            'titulo' => 'Subir trabajo',
            'texto' => 'Agrega evidencia a tu portafolio',
            'icono' => 'ri-folder-image-line',
            'color' => 'warning',
            'url' => route('mi-perfil-proveedor.portafolio.index'),
        ],
        [
            'permiso' => 'gestionar documentos proveedor',
            'titulo' => 'Mis documentos',
            'texto' => 'Gestiona tus archivos',
            'icono' => 'ri-file-list-3-line',
            'color' => 'info',
            'url' => route('mi-perfil-proveedor.documentos.index'),
        ],
        [
            'permiso' => 'gestionar especialidades proveedor',
            'titulo' => 'Mis especialidades',
            'texto' => 'Administra tus servicios',
            'icono' => 'ri-price-tag-3-line',
            'color' => 'primary',
            'url' => route('mi-perfil-proveedor.especialidades.index'),
        ],
    ];
@endphp

<div class="inicio-proveedor">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Hola, {{ $perfil->nombre_publico }}</h4>
                    <p class="text-muted mb-0">Gestiona tu perfil y tu presencia como proveedor.</p>
                </div>

                <span class="badge bg-{{ $estadoClass }}-subtle text-{{ $estadoClass }} text-capitalize fs-12">
                    {{ $perfil->estado_verificacion }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-5">
            <div class="card inicio-hero-card h-100">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h5 class="fw-bold mb-3">Bienvenido a tu panel</h5>
                            <p class="text-muted mb-4">
                                Desde aqui puedes administrar tu informacion, servicios y presencia en la plataforma.
                            </p>

                            @can('actualizar perfil proveedor')
                                <a href="{{ route('mi-perfil-proveedor.index') }}" class="btn btn-primary">
                                    <i class="ri-pencil-line align-bottom me-1"></i>
                                    Editar perfil
                                </a>
                            @endcan
                        </div>

                        <div class="col-md-5">
                            @if ($fotoPortada)
                                <div class="inicio-hero-image" style="background-image: url('{{ $fotoPortada }}');"></div>
                            @else
                                <div class="inicio-hero-placeholder">
                                    <i class="ri-briefcase-4-line"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card inicio-stat-card h-100">
                        <div class="card-body">
                            <div class="inicio-stat-icon bg-success-subtle text-success">
                                <i class="ri-shield-check-line"></i>
                            </div>
                            <p class="text-muted fw-medium mb-2">Perfil completo</p>
                            <h3 class="mb-2">{{ $data['perfilCompleto'] }}%</h3>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: {{ $data['perfilCompleto'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card inicio-stat-card h-100">
                        <div class="card-body">
                            <div class="inicio-stat-icon bg-info-subtle text-info">
                                <i class="ri-price-tag-3-line"></i>
                            </div>
                            <p class="text-muted fw-medium mb-2">Especialidades</p>
                            <h3 class="mb-1">{{ $data['especialidadesActivas'] }}</h3>
                            <small class="text-muted">Servicios activos</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card inicio-stat-card h-100">
                        <div class="card-body">
                            <div class="inicio-stat-icon bg-warning-subtle text-warning">
                                <i class="ri-gallery-line"></i>
                            </div>
                            <p class="text-muted fw-medium mb-2">Trabajos</p>
                            <h3 class="mb-1">{{ $data['trabajosPortafolio'] }}</h3>
                            <small class="text-muted">En portafolio</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card inicio-stat-card h-100">
                        <div class="card-body">
                            <div class="inicio-stat-icon bg-primary-subtle text-primary">
                                <i class="ri-file-shield-2-line"></i>
                            </div>
                            <p class="text-muted fw-medium mb-2">Documentos</p>
                            <h3 class="mb-1">{{ $data['documentosAprobados'] }}</h3>
                            <small class="text-muted">{{ $data['documentosPendientes'] }} pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">Acciones rapidas</h5>

                    <div class="row g-3">
                        @foreach ($acciones as $accion)
                            @can($accion['permiso'])
                                <div class="col-md-6 col-xl-4">
                                    <a href="{{ $accion['url'] }}" class="inicio-action-card">
                                        <span class="inicio-action-icon bg-{{ $accion['color'] }}-subtle text-{{ $accion['color'] }}">
                                            <i class="{{ $accion['icono'] }}"></i>
                                        </span>
                                        <span class="d-block fw-semibold text-body mt-3">{{ $accion['titulo'] }}</span>
                                        <span class="d-flex align-items-center justify-content-between text-muted fs-13 mt-2">
                                            {{ $accion['texto'] }}
                                            <i class="ri-arrow-right-line text-primary"></i>
                                        </span>
                                    </a>
                                </div>
                            @endcan
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">Actividad reciente</h5>

                    @forelse ($data['actividadReciente'] as $actividad)
                        <div class="inicio-activity-item">
                            <div class="inicio-activity-icon bg-success-subtle text-success">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $actividad->description }}</div>
                                <small class="text-muted">
                                    {{ $actividad->log_name }}
                                </small>
                            </div>
                            <small class="text-muted text-nowrap">
                                {{ $actividad->created_at->diffForHumans() }}
                            </small>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="avatar-sm mx-auto mb-3">
                                <span class="avatar-title rounded-circle bg-light text-muted">
                                    <i class="ri-history-line fs-20"></i>
                                </span>
                            </div>
                            <p class="text-muted mb-0">Aun no tienes actividad reciente.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card inicio-reminder-card mt-3">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="inicio-reminder-icon">
                        <i class="ri-information-line"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Manten tu perfil actualizado</h5>
                        <p class="text-muted mb-0">Un perfil completo y actualizado ayuda a generar mas confianza.</p>
                    </div>
                </div>

                @can('actualizar perfil proveedor')
                    <a href="{{ route('mi-perfil-proveedor.index') }}" class="btn btn-primary">
                        Completar perfil
                        <i class="ri-arrow-right-line align-bottom ms-1"></i>
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>
