@php
    $usuario = $data['usuario'];
    $acciones = [
        [
            'permiso' => 'buscar servicios',
            'titulo' => 'Buscar servicios',
            'texto' => 'Encuentra proveedores cerca de ti',
            'icono' => 'ri-search-eye-line',
            'color' => 'primary',
            'url' => route('cliente.buscar-servicios.index'),
        ],
        [
            'permiso' => 'ver mis solicitudes',
            'titulo' => 'Mis solicitudes',
            'texto' => 'Revisa tus solicitudes enviadas',
            'icono' => 'ri-file-list-3-line',
            'color' => 'info',
            'url' => route('cliente.solicitudes.index'),
        ],
        [
            'permiso' => 'ver mis citas',
            'titulo' => 'Mis citas',
            'texto' => 'Consulta tus citas programadas',
            'icono' => 'ri-calendar-check-line',
            'color' => 'success',
            'url' => route('cliente.solicitudes.index', ['tab' => 'citas']),
        ],
        [
            'permiso' => 'ver mis calificaciones',
            'titulo' => 'Mis calificaciones',
            'texto' => 'Califica servicios finalizados',
            'icono' => 'ri-star-smile-line',
            'color' => 'warning',
            'url' => route('cliente.calificaciones.index'),
        ],
    ];
@endphp

<div class="inicio-cliente">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Hola, {{ $usuario->name }}</h4>
                    <p class="text-muted mb-0">Busca proveedores, revisa tus solicitudes y sigue tus citas desde aqui.</p>
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
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('perfil.index') }}" class="inicio-action-card">
                                <span class="inicio-action-icon bg-primary-subtle text-primary">
                                    <i class="ri-user-settings-line"></i>
                                </span>
                                <span class="d-block fw-semibold text-body mt-3">Editar perfil</span>
                                <span class="d-flex align-items-center justify-content-between text-muted fs-13 mt-2">
                                    Actualiza tu informacion principal
                                    <i class="ri-arrow-right-line text-primary"></i>
                                </span>
                            </a>
                        </div>

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
            @include('inicio.partials.cliente-proveedores-atendidos', [
                'proveedores' => $data['proveedoresAtendidos'],
            ])
        </div>
    </div>

    @if (! empty($data['resumenCitas']))
        @include('inicio.partials.citas-grafica', [
            'resumenCitas' => $data['resumenCitas'],
            'titulo' => 'Resumen de tus citas',
            'descripcion' => 'Movimiento general de las citas que solicitaste durante ' . $data['resumenCitas']['anio'] . '.',
        ])
    @endif
</div>
