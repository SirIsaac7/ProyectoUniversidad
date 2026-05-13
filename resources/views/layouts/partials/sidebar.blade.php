<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="Logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="Logo" height="17">
            </span>
        </a>

        <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="Logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" height="17">
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
                <li class="menu-title"><span>Menu</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @can('ver usuarios')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                            <i class="ri-user-settings-line"></i>
                            <span>Usuarios</span>
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

                @can('ver roles')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <i class="ri-admin-line"></i>
                            <span>Roles</span>
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
                    auth()->user()->can('ver proveedores') ||
                    auth()->user()->can('ver especialidades proveedor') ||
                    auth()->user()->can('ver horarios proveedor') ||
                    auth()->user()->can('ver ubicaciones proveedor') ||
                    auth()->user()->can('ver portafolio proveedor') ||
                    auth()->user()->can('ver tipos documento proveedor') ||
                    auth()->user()->can('ver documentos proveedor') ||
                    auth()->user()->can('ver mi perfil proveedor')
                )
                <li class="nav-item">
                    <a
                        class="nav-link menu-link {{ request()->routeIs('perfiles-proveedores.*') || request()->routeIs('proveedor-especialidades.*') || request()->routeIs('horarios-proveedor.*') || request()->routeIs('ubicaciones-proveedor.*') || request()->routeIs('portafolio-proveedor.*') || request()->routeIs('tipos-documento-proveedor.*') || request()->routeIs('documentos-proveedor.*') || request()->routeIs('mi-perfil-proveedor.*') ? 'active' : '' }}"
                        href="#sidebarProveedores"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="{{ request()->routeIs('perfiles-proveedores.*') || request()->routeIs('proveedor-especialidades.*') || request()->routeIs('horarios-proveedor.*') || request()->routeIs('ubicaciones-proveedor.*') || request()->routeIs('portafolio-proveedor.*') || request()->routeIs('tipos-documento-proveedor.*') || request()->routeIs('documentos-proveedor.*') || request()->routeIs('mi-perfil-proveedor.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarProveedores"
                    >
                        <i class="ri-briefcase-4-line"></i>
                        <span>Proveedores</span>
                    </a>

                    <div
                        class="collapse menu-dropdown {{ request()->routeIs('perfiles-proveedores.*') || request()->routeIs('proveedor-especialidades.*') || request()->routeIs('horarios-proveedor.*') || request()->routeIs('ubicaciones-proveedor.*') || request()->routeIs('portafolio-proveedor.*') || request()->routeIs('tipos-documento-proveedor.*') || request()->routeIs('documentos-proveedor.*') || request()->routeIs('mi-perfil-proveedor.*') ? 'show' : '' }}"
                        id="sidebarProveedores"
                    >
                        <ul class="nav nav-sm flex-column">
                            @can('ver mi perfil proveedor')
                                <li class="nav-item">
                                    <a href="{{ route('mi-perfil-proveedor.index') }}" class="nav-link {{ request()->routeIs('mi-perfil-proveedor.*') ? 'active' : '' }}">
                                        <i class="ri-user-heart-line me-1"></i>
                                        Mi perfil
                                    </a>
                                </li>
                            @endcan

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
