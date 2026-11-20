<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm1.png') }}" alt="Logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/LogoTecnoConexion.png') }}" alt="Logo" height="50">
            </span>
        </a>

        <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm1.png') }}" alt="Logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/LogoTecnoConexion.png') }}" alt="Logo" height="50">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                @php
                    $usuarioActual = auth()->user();
                    $puedeVerMiPerfilProveedor = $usuarioActual->can('visualizar perfil proveedor');
                    $puedeVerAdministracionProveedores = ! $usuarioActual->hasRole('PROVEEDOR') && (
                        $usuarioActual->can('ver proveedores') ||
                        $usuarioActual->can('ver especialidades proveedor') ||
                        $usuarioActual->can('ver horarios proveedor') ||
                        $usuarioActual->can('ver ubicaciones proveedor') ||
                        $usuarioActual->can('ver portafolio proveedor') ||
                        $usuarioActual->can('ver tipos documento proveedor') ||
                        $usuarioActual->can('ver documentos proveedor')
                    );
                    $puedeVerMenuMiPerfilProveedor = $puedeVerMiPerfilProveedor && ! $puedeVerAdministracionProveedores;
                @endphp

                <li class="menu-title"><span>Principal</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('inicio') ? 'active' : '' }}" href="{{ route('inicio') }}">
                        <i class="ri-home-4-line"></i>
                        <span>Inicio</span>
                    </a>
                </li>

                @can('ver mis notificaciones')
                    @php
                        $totalNotificacionesNoLeidas = auth()->user()->unreadNotifications()->count();
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('notificaciones.*') ? 'active' : '' }}" href="{{ route('notificaciones.index') }}">
                            <i class="ri-notification-3-line"></i>
                            <span>Notificaciones</span>
                            @if ($totalNotificacionesNoLeidas > 0)
                                <span class="badge bg-danger-subtle text-danger ms-auto">{{ $totalNotificacionesNoLeidas }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                @if (
                    $usuarioActual->can('ver usuarios') ||
                    $usuarioActual->can('ver roles') ||
                    $usuarioActual->can('ver permisos') ||
                    $usuarioActual->can('ver activity logs') ||
                    $usuarioActual->can('ver backups') ||
                    $usuarioActual->can('ver configuracion correos')
                )
                    <li class="menu-title"><span>Administración del sistema</span></li>
                @endif

                @can('ver usuarios')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                            <i class="ri-user-settings-line"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
                @endcan

                @can('ver roles')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <i class="ri-admin-line"></i>
                            <span>Roles</span>
                        </a>
                    </li>
                @endcan

                @can('ver permisos')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('permisos.*') ? 'active' : '' }}" href="{{ route('permisos.index') }}">
                            <i class="ri-shield-keyhole-line"></i>
                            <span>Permisos</span>
                        </a>
                    </li>
                @endcan

                @can('ver activity logs')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('activitylogs.*') ? 'active' : '' }}" href="{{ route('activitylogs.index') }}">
                            <i class="ri-history-line"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                @endcan

                @can('ver backups')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('backups.*') ? 'active' : '' }}" href="{{ route('backups.index') }}">
                            <i class="ri-database-2-line"></i>
                            <span>Backups</span>
                        </a>
                    </li>
                @endcan

                @can('ver configuracion correos')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('configuracion-correos.*') ? 'active' : '' }}" href="{{ route('configuracion-correos.edit') }}">
                            <i class="ri-mail-settings-line"></i>
                            <span>Correos</span>
                        </a>
                    </li>
                @endcan

                @if (
                    $usuarioActual->can('ver rubros') ||
                    $usuarioActual->can('ver tipos servicio') ||
                    $usuarioActual->can('ver especialidades') ||
                    $puedeVerAdministracionProveedores ||
                    $usuarioActual->can('ver solicitudes') ||
                    $usuarioActual->can('ver citas') ||
                    $usuarioActual->can('ver calificaciones') ||
                    $usuarioActual->can('ver aspectos calificacion')
                )
                    <li class="menu-title"><span>Gestión operativa</span></li>
                @endif

                @if (
                    auth()->user()->can('ver rubros') ||
                    auth()->user()->can('ver tipos servicio') ||
                    auth()->user()->can('ver especialidades')
                )
                <li class="nav-item">
                    <a
                        class="nav-link menu-link {{ request()->routeIs('rubros.*') || request()->routeIs('tipos-servicio.*') || request()->routeIs('especialidades.*') ? 'active' : '' }}"
                        href="#sidebarCatalogoServicios"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="{{ request()->routeIs('rubros.*') || request()->routeIs('tipos-servicio.*') || request()->routeIs('especialidades.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarCatalogoServicios"
                    >
                        <i class="ri-apps-2-line"></i>
                        <span>Catálogo de servicios</span>
                    </a>

                    <div
                        class="collapse menu-dropdown {{ request()->routeIs('rubros.*') || request()->routeIs('tipos-servicio.*') || request()->routeIs('especialidades.*') ? 'show' : '' }}"
                        id="sidebarCatalogoServicios"
                    >
                        <ul class="nav nav-sm flex-column">
                            @can('ver rubros')
                                <li class="nav-item">
                                    <a href="{{ route('rubros.index') }}" class="nav-link {{ request()->routeIs('rubros.*') ? 'active' : '' }}">
                                        <i class="ri-apps-2-line me-1"></i>
                                        Rubros
                                    </a>
                                </li>
                            @endcan

                            @can('ver tipos servicio')
                                <li class="nav-item">
                                    <a href="{{ route('tipos-servicio.index') }}" class="nav-link {{ request()->routeIs('tipos-servicio.*') ? 'active' : '' }}">
                                        <i class="ri-service-line me-1"></i>
                                        Tipos de servicio
                                    </a>
                                </li>
                            @endcan

                            @can('ver especialidades')
                                <li class="nav-item">
                                    <a href="{{ route('especialidades.index') }}" class="nav-link {{ request()->routeIs('especialidades.*') ? 'active' : '' }}">
                                        <i class="ri-price-tag-3-line me-1"></i>
                                        Especialidades
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endif

                @if (
                    $usuarioActual->hasRole('PROVEEDOR') && (
                        $usuarioActual->can('ver mis solicitudes proveedor') ||
                        $usuarioActual->can('ver mis calificaciones proveedor')
                    )
                )
                    <li class="menu-title"><span>Gestión del servicio</span></li>
                @endif

                @if ($usuarioActual->hasRole('PROVEEDOR') && $usuarioActual->can('ver mis solicitudes proveedor'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('proveedor.solicitudes.*') || request()->routeIs('proveedor.citas.*') || request()->routeIs('proveedor.historial-solicitudes.*') ? 'active' : '' }}" href="{{ route('proveedor.solicitudes.index') }}">
                            <i class="ri-inbox-archive-line"></i>
                            <span>Mis solicitudes</span>
                        </a>
                    </li>
                @endif

                @if ($usuarioActual->hasRole('PROVEEDOR') && $usuarioActual->can('ver mis calificaciones proveedor'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('proveedor.calificaciones.*') ? 'active' : '' }}" href="{{ route('proveedor.calificaciones.index') }}">
                            <i class="ri-star-smile-line"></i>
                            <span>Mis calificaciones</span>
                        </a>
                    </li>
                @endif

                @if ($puedeVerMenuMiPerfilProveedor)
                    <li class="menu-title"><span>Perfil profesional</span></li>

                    <li class="nav-item">
                        <a href="{{ route('mi-perfil-proveedor.index') }}" class="nav-link menu-link {{ request()->routeIs('mi-perfil-proveedor.index') ? 'active' : '' }}">
                            <i class="ri-user-heart-line"></i>
                            <span>Datos Generales</span>
                        </a>
                    </li>

                    @can('gestionar especialidades proveedor')
                        <li class="nav-item">
                            <a href="{{ route('mi-perfil-proveedor.especialidades.index') }}" class="nav-link menu-link {{ request()->routeIs('mi-perfil-proveedor.especialidades.*') ? 'active' : '' }}">
                                <i class="ri-price-tag-3-line"></i>
                                <span>Mis especialidades</span>
                            </a>
                        </li>
                    @endcan

                    @can('gestionar horarios proveedor')
                        <li class="nav-item">
                            <a href="{{ route('mi-perfil-proveedor.horarios.index') }}" class="nav-link menu-link {{ request()->routeIs('mi-perfil-proveedor.horarios.*') ? 'active' : '' }}">
                                <i class="ri-calendar-schedule-line"></i>
                                <span>Mis horarios</span>
                            </a>
                        </li>
                    @endcan

                    @can('gestionar ubicacion proveedor')
                        <li class="nav-item">
                            <a href="{{ route('mi-perfil-proveedor.ubicacion.index') }}" class="nav-link menu-link {{ request()->routeIs('mi-perfil-proveedor.ubicacion.*') ? 'active' : '' }}">
                                <i class="ri-map-pin-line"></i>
                                <span>Mi ubicación</span>
                            </a>
                        </li>
                    @endcan

                    @can('gestionar portafolio proveedor')
                        <li class="nav-item">
                            <a href="{{ route('mi-perfil-proveedor.portafolio.index') }}" class="nav-link menu-link {{ request()->routeIs('mi-perfil-proveedor.portafolio.*') ? 'active' : '' }}">
                                <i class="ri-folder-image-line"></i>
                                <span>Mi portafolio</span>
                            </a>
                        </li>
                    @endcan

                    @can('gestionar documentos proveedor')
                        <li class="nav-item">
                            <a href="{{ route('mi-perfil-proveedor.documentos.index') }}" class="nav-link menu-link {{ request()->routeIs('mi-perfil-proveedor.documentos.*') ? 'active' : '' }}">
                                <i class="ri-file-list-3-line"></i>
                                <span>Mis documentos</span>
                            </a>
                        </li>
                    @endcan
                @endif

                @if ($puedeVerAdministracionProveedores)
                <li class="nav-item">
                    <a
                        class="nav-link menu-link {{ request()->routeIs('perfiles-proveedores.*') || request()->routeIs('proveedor-especialidades.*') || request()->routeIs('horarios-proveedor.*') || request()->routeIs('ubicaciones-proveedor.*') || request()->routeIs('portafolio-proveedor.*') || request()->routeIs('tipos-documento-proveedor.*') || request()->routeIs('documentos-proveedor.*') ? 'active' : '' }}"
                        href="#sidebarProveedores"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="{{ request()->routeIs('perfiles-proveedores.*') || request()->routeIs('proveedor-especialidades.*') || request()->routeIs('horarios-proveedor.*') || request()->routeIs('ubicaciones-proveedor.*') || request()->routeIs('portafolio-proveedor.*') || request()->routeIs('tipos-documento-proveedor.*') || request()->routeIs('documentos-proveedor.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarProveedores"
                    >
                        <i class="ri-briefcase-4-line"></i>
                        <span>Proveedores</span>
                    </a>

                    <div
                        class="collapse menu-dropdown {{ request()->routeIs('perfiles-proveedores.*') || request()->routeIs('proveedor-especialidades.*') || request()->routeIs('horarios-proveedor.*') || request()->routeIs('ubicaciones-proveedor.*') || request()->routeIs('portafolio-proveedor.*') || request()->routeIs('tipos-documento-proveedor.*') || request()->routeIs('documentos-proveedor.*') ? 'show' : '' }}"
                        id="sidebarProveedores"
                    >
                        <ul class="nav nav-sm flex-column">
                            @if ($puedeVerAdministracionProveedores)
                                @can('ver proveedores')
                                    <li class="nav-item">
                                        <a href="{{ route('perfiles-proveedores.index') }}" class="nav-link {{ request()->routeIs('perfiles-proveedores.*') ? 'active' : '' }}">
                                            <i class="ri-user-star-line me-1"></i>
                                            Perfiles
                                        </a>
                                    </li>
                                @endcan

                                @can('ver especialidades proveedor')
                                    <li class="nav-item">
                                        <a href="{{ route('proveedor-especialidades.index') }}" class="nav-link {{ request()->routeIs('proveedor-especialidades.*') ? 'active' : '' }}">
                                            <i class="ri-price-tag-3-line me-1"></i>
                                            Especialidades
                                        </a>
                                    </li>
                                @endcan

                                @can('ver horarios proveedor')
                                    <li class="nav-item">
                                        <a href="{{ route('horarios-proveedor.index') }}" class="nav-link {{ request()->routeIs('horarios-proveedor.*') ? 'active' : '' }}">
                                            <i class="ri-calendar-schedule-line me-1"></i>
                                            Horarios
                                        </a>
                                    </li>
                                @endcan

                                @can('ver ubicaciones proveedor')
                                    <li class="nav-item">
                                        <a href="{{ route('ubicaciones-proveedor.index') }}" class="nav-link {{ request()->routeIs('ubicaciones-proveedor.*') ? 'active' : '' }}">
                                            <i class="ri-map-pin-line me-1"></i>
                                            Ubicaciones
                                        </a>
                                    </li>
                                @endcan

                                @can('ver portafolio proveedor')
                                    <li class="nav-item">
                                        <a href="{{ route('portafolio-proveedor.index') }}" class="nav-link {{ request()->routeIs('portafolio-proveedor.*') ? 'active' : '' }}">
                                            <i class="ri-gallery-line me-1"></i>
                                            Portafolio
                                        </a>
                                    </li>
                                @endcan

                                @can('ver tipos documento proveedor')
                                    <li class="nav-item">
                                        <a href="{{ route('tipos-documento-proveedor.index') }}" class="nav-link {{ request()->routeIs('tipos-documento-proveedor.*') ? 'active' : '' }}">
                                            <i class="ri-file-list-3-line me-1"></i>
                                            Tipos de documento
                                        </a>
                                    </li>
                                @endcan

                                @can('ver documentos proveedor')
                                    <li class="nav-item">
                                        <a href="{{ route('documentos-proveedor.index') }}" class="nav-link {{ request()->routeIs('documentos-proveedor.*') ? 'active' : '' }}">
                                            <i class="ri-file-shield-2-line me-1"></i>
                                            Documentos
                                        </a>
                                    </li>
                                @endcan
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if ($usuarioActual->can('ver solicitudes') || $usuarioActual->can('ver citas'))
                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('solicitudes.*') || request()->routeIs('citas.*') || request()->routeIs('historial-solicitudes.*') ? 'active' : '' }}"
                            href="#sidebarCitas"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="{{ request()->routeIs('solicitudes.*') || request()->routeIs('citas.*') || request()->routeIs('historial-solicitudes.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarCitas"
                        >
                            <i class="ri-calendar-event-line"></i>
                            <span>Citas</span>
                        </a>

                        <div
                            class="collapse menu-dropdown {{ request()->routeIs('solicitudes.*') || request()->routeIs('citas.*') || request()->routeIs('historial-solicitudes.*') ? 'show' : '' }}"
                            id="sidebarCitas"
                        >
                            <ul class="nav nav-sm flex-column">
                                @can('ver solicitudes')
                                    <li class="nav-item">
                                        <a href="{{ route('solicitudes.index') }}" class="nav-link {{ request()->routeIs('solicitudes.*') ? 'active' : '' }}">
                                            <i class="ri-calendar-check-line me-1"></i>
                                            Solicitudes
                                        </a>
                                    </li>
                                @endcan

                                @can('ver citas')
                                    <li class="nav-item">
                                        <a href="{{ route('citas.index') }}" class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}">
                                            <i class="ri-calendar-event-line me-1"></i>
                                            Citas
                                        </a>
                                    </li>
                                @endcan

                                @can('ver solicitudes')
                                    <li class="nav-item">
                                        <a href="{{ route('historial-solicitudes.index') }}" class="nav-link {{ request()->routeIs('historial-solicitudes.*') ? 'active' : '' }}">
                                            <i class="ri-time-line me-1"></i>
                                            Historial
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endif

                @if ($usuarioActual->can('ver calificaciones') || $usuarioActual->can('ver aspectos calificacion'))
                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('calificaciones.*') || request()->routeIs('aspectos-calificacion.*') ? 'active' : '' }}"
                            href="#sidebarCalificaciones"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="{{ request()->routeIs('calificaciones.*') || request()->routeIs('aspectos-calificacion.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarCalificaciones"
                        >
                            <i class="ri-star-smile-line"></i>
                            <span>Calificaciones</span>
                        </a>

                        <div
                            class="collapse menu-dropdown {{ request()->routeIs('calificaciones.*') || request()->routeIs('aspectos-calificacion.*') ? 'show' : '' }}"
                            id="sidebarCalificaciones"
                        >
                            <ul class="nav nav-sm flex-column">
                                @can('ver calificaciones')
                                    <li class="nav-item">
                                        <a href="{{ route('calificaciones.index') }}" class="nav-link {{ request()->routeIs('calificaciones.*') ? 'active' : '' }}">
                                            <i class="ri-message-3-line me-1"></i>
                                            Reseñas
                                        </a>
                                    </li>
                                @endcan

                                @can('ver aspectos calificacion')
                                    <li class="nav-item">
                                        <a href="{{ route('aspectos-calificacion.index') }}" class="nav-link {{ request()->routeIs('aspectos-calificacion.*') ? 'active' : '' }}">
                                            <i class="ri-list-check-3 me-1"></i>
                                            Aspectos
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @if ($usuarioActual->can('ver panel motor busqueda') || $usuarioActual->can('ver reportes'))
                    <li class="menu-title"><span>Análisis y automatización</span></li>
                @endif

                @can('ver panel motor busqueda')
                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('motor-busqueda.*') ? 'active' : 'collapsed' }}"
                            href="#sidebarMotorBusqueda"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="{{ request()->routeIs('motor-busqueda.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarMotorBusqueda"
                        >
                            <i class="ri-cpu-line"></i>
                            <span>Motor inteligente</span>
                        </a>

                        <div
                            class="collapse menu-dropdown {{ request()->routeIs('motor-busqueda.*') ? 'show' : '' }}"
                            id="sidebarMotorBusqueda"
                        >
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('motor-busqueda.index') }}" class="nav-link {{ request()->routeIs('motor-busqueda.index') || request()->routeIs('motor-busqueda.estado') ? 'active' : '' }}">
                                        <i class="ri-dashboard-line me-1"></i>Resumen y servicios
                                    </a>
                                </li>

                                @if (auth()->user()->can('ejecutar indexacion motor busqueda') || auth()->user()->can('consultar estado motor busqueda'))
                                    <li class="nav-item">
                                        <a href="{{ route('motor-busqueda.indexacion.index') }}" class="nav-link {{ request()->routeIs('motor-busqueda.indexacion.*') ? 'active' : '' }}">
                                            <i class="ri-refresh-line me-1"></i>Indexación manual
                                        </a>
                                    </li>
                                @endif

                                @can('configurar programacion indexaciones motor')
                                    <li class="nav-item">
                                        <a href="{{ route('motor-busqueda.programacion.edit') }}" class="nav-link {{ request()->routeIs('motor-busqueda.programacion.*') ? 'active' : '' }}">
                                            <i class="ri-calendar-schedule-line me-1"></i>Programación
                                        </a>
                                    </li>
                                @endcan

                                @can('ver historial indexaciones motor')
                                    <li class="nav-item">
                                        <a href="{{ route('motor-busqueda.historial.index') }}" class="nav-link {{ request()->routeIs('motor-busqueda.historial.*') ? 'active' : '' }}">
                                            <i class="ri-history-line me-1"></i>Historial
                                        </a>
                                    </li>
                                @endcan

                                @can('ver personalizacion busqueda')
                                    <li class="nav-item">
                                        <a href="{{ route('motor-busqueda.personalizacion.edit') }}" class="nav-link {{ request()->routeIs('motor-busqueda.personalizacion.*') ? 'active' : '' }}">
                                            <i class="ri-palette-line me-1"></i>Personalización
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('ver reportes')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
                            <i class="ri-file-chart-line"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                @endcan

                @if (
                    $usuarioActual->hasRole('CLIENTE') && (
                        $usuarioActual->can('buscar servicios') ||
                        $usuarioActual->can('ver mis solicitudes') ||
                        $usuarioActual->can('ver mis calificaciones')
                    )
                )
                    <li class="menu-title"><span>Servicios</span></li>
                @endif

                @if ($usuarioActual->hasRole('CLIENTE') && $usuarioActual->can('buscar servicios'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('cliente.buscar-servicios.*') ? 'active' : '' }}" href="{{ route('cliente.buscar-servicios.index') }}">
                            <i class="ri-search-eye-line"></i>
                            <span>Buscar servicios</span>
                        </a>
                    </li>
                @endif

                @if ($usuarioActual->hasRole('CLIENTE') && $usuarioActual->can('ver mis solicitudes'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('cliente.solicitudes.*') ? 'active' : '' }}" href="{{ route('cliente.solicitudes.index') }}">
                            <i class="ri-file-list-2-line"></i>
                            <span>Mis solicitudes</span>
                        </a>
                    </li>
                @endif

                @if ($usuarioActual->hasRole('CLIENTE') && $usuarioActual->can('ver mis calificaciones'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('cliente.calificaciones.*') ? 'active' : '' }}" href="{{ route('cliente.calificaciones.index') }}">
                            <i class="ri-star-smile-line"></i>
                            <span>Mis calificaciones</span>
                        </a>
                    </li>
                @endif

                @if ($usuarioActual->hasRole('CLIENTE'))
                    <li class="menu-title"><span>Otros</span></li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link collapsed"
                            href="#sidebarClienteExtras"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="false"
                            aria-controls="sidebarClienteExtras"
                        >
                            <i class="ri-layout-grid-line"></i>
                            <span>Mas modulos</span>
                        </a>

                        <div class="collapse menu-dropdown" id="sidebarClienteExtras">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link">
                                        <i class="ri-heart-3-line me-1"></i>
                                        Favoritos
                                        <span class="badge bg-secondary-subtle text-secondary ms-auto">Pronto</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link">
                                        <i class="ri-map-2-line me-1"></i>
                                        Direcciones guardadas
                                        <span class="badge bg-secondary-subtle text-secondary ms-auto">Pronto</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link">
                                        <i class="ri-shield-check-line me-1"></i>
                                        Garantias y soporte
                                        <span class="badge bg-secondary-subtle text-secondary ms-auto">Pronto</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link">
                                        <i class="ri-customer-service-2-line me-1"></i>
                                        Centro de ayuda
                                        <span class="badge bg-secondary-subtle text-secondary ms-auto">Pronto</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
