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

                @can('ver rubros')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('rubros.*') ? 'active' : '' }}" href="{{ route('rubros.index') }}">
                        <i class="ri-apps-2-line"></i>
                        <span>Rubros</span>
                    </a>
                </li>
                @endcan

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
