<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('inicio') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="Logo" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="Logo" height="17">
                        </span>
                    </a>

                    <a href="{{ route('inicio') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="Logo" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

            </div>

            <div class="d-flex align-items-center">

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger d-none js-notificaciones-contador">
                            0
                            <span class="visually-hidden">notificaciones</span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 topbar-notificaciones-dropdown" aria-labelledby="page-header-notifications-dropdown">
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white"> Notificaciones </h6>
                                    </div>
                                    <div class="col-auto dropdown-tabs">
                                        <span class="badge bg-light-subtle text-body fs-13 js-notificaciones-resumen">0 nuevas</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar class="pe-2 js-notificaciones-lista topbar-notificaciones-lista">
                            <div class="text-center py-4 js-notificaciones-vacio">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                        <i class="bx bx-bell-off fs-24"></i>
                                    </span>
                                </div>
                                <h6 class="mb-1">Sin notificaciones</h6>
                                <p class="text-muted mb-0 fs-12">Aqui veras los avisos importantes del sistema.</p>
                            </div>
                        </div>
                        <div class="p-2 border-top border-top-dashed">
                            <div class="d-grid">
                                <button type="button" class="btn btn-soft-success btn-sm js-notificaciones-leer-todas">
                                    Marcar todas como leidas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            @if (auth()->user()?->avatar_url)
                                <img
                                    class="rounded-circle flex-shrink-0"
                                    src="{{ auth()->user()->avatar_url }}"
                                    alt="Avatar"
                                    width="32"
                                    height="32"
                                    referrerpolicy="no-referrer"
                                    style="object-fit: cover;"
                                >
                            @else
                                <span
                                    class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-semibold"
                                    style="width: 32px; height: 32px; min-width: 32px;"
                                >
                                    {{ auth()->user()?->inicial ?? 'U' }}
                                </span>
                            @endif
                            <span class="text-start ms-2 lh-sm">
                                <span class="d-block fw-semibold user-name-text">{{ auth()->user()->name ?? 'Usuario' }}</span>
                                <span class="d-block fs-12 user-name-sub-text">Cuenta activa</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Bienvenido</h6>
                        <a class="dropdown-item" href="{{ url('/') }}">
                            <i class="mdi mdi-home-circle text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Inicio</span>
                        </a>
                        <a class="dropdown-item" href="{{ route('perfil.index') }}">
                            <i class="ri-user-line text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Mi perfil</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">Cerrar sesion</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
