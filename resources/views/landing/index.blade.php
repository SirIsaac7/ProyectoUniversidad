<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TecnoConexion | Servicios profesionales en La Paz</title>
    <meta name="description" content="Conecta con proveedores verificados para tecnologia, hogar y servicios profesionales en La Paz.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>

<body data-bs-spy="scroll" data-bs-target=".landing-navbar" class="landing-loading">
    <div class="landing-splash" id="landingSplash" role="status" aria-live="polite">
        <div class="splash-orbit">
            <span></span>
            <span></span>
            <span></span>
            <img src="{{ asset('assets/images/LogoTecnoConexion.png') }}" alt="TecnoConexion">
        </div>
        <h1>Bienvenido a TecnoConexion</h1>
        <p>Conectando servicios confiables en La Paz</p>
        <div class="splash-loader"><span></span></div>
    </div>

    <div class="landing-noise"></div>
    <div class="cursor-glow" aria-hidden="true"></div>

    <nav class="navbar navbar-expand-lg landing-navbar fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#" aria-label="TecnoConexion">
                <img src="{{ asset('assets/images/LogoTecnoConexion.png') }}" alt="TecnoConexion" class="brand-logo">
                <span>TecnoConexion</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav"
                aria-controls="landingNav" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="landingNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#about">Como funciona</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#providers">Proveedores</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reviews">Reseñas</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contacto</a></li>
                </ul>

                <div class="d-flex gap-2 ms-lg-4 mt-3 mt-lg-0">
                    @auth
                        <a href="{{ route('inicio') }}" class="btn btn-neon">Ir al panel</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost">Ingresar</a>
                        <a href="{{ route('register') }}" class="btn btn-neon">Registrarse</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section id="hero" class="hero-section min-vh-100 d-flex align-items-center">
            <div class="hero-orbit hero-orbit-one"></div>
            <div class="hero-orbit hero-orbit-two"></div>
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7">
                        <div class="hero-copy" data-aos="fade-up">
                            <span class="eyebrow">
                                <i class="ri-shield-check-line"></i>
                                Proveedores verificados en La Paz
                            </span>
                            <h1>Encuentra al profesional ideal para resolver tu servicio.</h1>
                            <p>
                                Busca proveedores de confianza, revisa su experiencia y solicita atencion en pocos
                                pasos. Todo pensado para La Paz y para servicios reales de cada dia.
                            </p>

                            <div class="hero-actions">
                                <a href="{{ route('register') }}" class="btn btn-neon btn-lg">
                                    Empezar ahora
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                                <a href="#services" class="btn btn-glass btn-lg">
                                    Ver servicios
                                    <i class="ri-search-eye-line"></i>
                                </a>
                            </div>

                            <div class="hero-metrics">
                                <div>
                                    <strong data-count="24">0</strong>
                                    <span>horas para solicitar</span>
                                </div>
                                <div>
                                    <strong data-count="5">0</strong>
                                    <span>pasos simples</span>
                                </div>
                                <div>
                                    <strong data-count="100">0</strong>
                                    <span>% seguimiento</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-device" data-aos="zoom-in" data-aos-delay="120">
                            <div class="device-map">
                                <img src="{{ asset('assets/images/Home.jpg') }}" alt="Servicios en La Paz">
                                <div class="service-flow-animation" aria-hidden="true">
                                    <div class="flow-node flow-client">
                                        <i class="ri-user-search-line"></i>
                                        <span>Cliente</span>
                                    </div>
                                    <div class="flow-line flow-line-one"></div>
                                    <div class="flow-node flow-provider">
                                        <i class="ri-shield-user-line"></i>
                                        <span>Proveedor</span>
                                    </div>
                                    <div class="flow-line flow-line-two"></div>
                                    <div class="flow-node flow-calendar">
                                        <i class="ri-calendar-check-line"></i>
                                        <span>Cita</span>
                                    </div>
                                    <div class="flow-pulse"></div>
                                </div>
                            </div>
                            <div class="device-card">
                                <i class="ri-calendar-check-line"></i>
                                <div>
                                    <strong>Solicitud lista</strong>
                                    <span>Elige proveedor, agenda y recibe seguimiento.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="section-padding platform-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-5" data-aos="fade-right">
                        <div class="image-frame">
                            <img src="{{ asset('assets/images/FotoPersonal.jpg') }}" alt="Proyecto TecnoConexion">
                        </div>
                    </div>
                    <div class="col-lg-7" data-aos="fade-left">
                        <span class="eyebrow dark">
                            <i class="ri-rocket-2-line"></i>
                            Simple y confiable
                        </span>
                        <h2>Todo lo que necesitas para contratar con mas tranquilidad.</h2>
                        <p>
                            Compara proveedores, revisa su perfil, confirma su zona de atencion y sigue tu solicitud
                            hasta que el servicio quede atendido.
                        </p>

                        <div class="feature-grid">
                            <div class="feature-card">
                                <i class="ri-user-star-line"></i>
                                <h5>Proveedores confiables</h5>
                                <p>Perfiles con experiencia, documentos, trabajos realizados y calificaciones.</p>
                            </div>
                            <div class="feature-card">
                                <i class="ri-map-pin-range-line"></i>
                                <h5>Atencion cerca de ti</h5>
                                <p>Encuentra profesionales por zona y verifica si pueden atender tu ubicacion.</p>
                            </div>
                            <div class="feature-card">
                                <i class="ri-notification-3-line"></i>
                                <h5>Avisos oportunos</h5>
                                <p>Recibe actualizaciones cuando tu solicitud avance, sea aceptada o finalice.</p>
                            </div>
                            <div class="feature-card">
                                <i class="ri-bar-chart-box-line"></i>
                                <h5>Servicio ordenado</h5>
                                <p>Historial, citas, documentos y reputacion en un solo lugar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="services" class="section-padding services-section">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <span class="eyebrow dark">Servicios</span>
                    <h2>Soluciones para tecnologia, hogar y negocios.</h2>
                    <p>Elige el tipo de ayuda que necesitas y encuentra proveedores disponibles para atenderte.</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
                        <article class="service-card">
                            <i class="ri-computer-line"></i>
                            <h5>Tecnologia</h5>
                            <p>Reparacion, mantenimiento, diagnostico, redes, camaras y soporte tecnico.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="120">
                        <article class="service-card">
                            <i class="ri-home-gear-line"></i>
                            <h5>Hogar</h5>
                            <p>Encuentra ayuda para instalaciones, reparaciones y atencion a domicilio.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="190">
                        <article class="service-card">
                            <i class="ri-briefcase-4-line"></i>
                            <h5>Negocios</h5>
                            <p>Soporte para equipos, mantenimiento, instalaciones y necesidades operativas.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="260">
                        <article class="service-card">
                            <i class="ri-customer-service-2-line"></i>
                            <h5>Asistencia</h5>
                            <p>Solicita, coordina, recibe atencion y califica la experiencia al finalizar.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="impact-section">
            <div class="container">
                <div class="impact-panel">
                    <div class="impact-item" data-aos="fade-up">
                        <i class="ri-search-eye-line"></i>
                        <strong>Busca</strong>
                        <span>Describe que necesitas y encuentra opciones cercanas.</span>
                    </div>
                    <div class="impact-item" data-aos="fade-up" data-aos-delay="80">
                        <i class="ri-user-star-line"></i>
                        <strong>Compara</strong>
                        <span>Revisa perfiles, trabajos, documentos y calificaciones.</span>
                    </div>
                    <div class="impact-item" data-aos="fade-up" data-aos-delay="160">
                        <i class="ri-calendar-check-line"></i>
                        <strong>Agenda</strong>
                        <span>Coordina fecha, hora y tipo de atencion con el proveedor.</span>
                    </div>
                    <div class="impact-item" data-aos="fade-up" data-aos-delay="240">
                        <i class="ri-star-smile-line"></i>
                        <strong>Califica</strong>
                        <span>Comparte tu experiencia para ayudar a otros clientes.</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="providers" class="section-padding providers-section">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <span class="eyebrow dark">Proveedores</span>
                    <h2>Conoce a quien va a atender tu solicitud.</h2>
                    <p>Antes de contratar, revisa especialidades, ubicacion, horarios, trabajos realizados y documentos.</p>
                </div>

                <div class="row g-4">
                    @foreach ([
                        ['img' => 'person-1.jpg', 'name' => 'Soporte tecnico', 'tag' => 'Diagnostico'],
                        ['img' => 'person-2.jpg', 'name' => 'Redes y camaras', 'tag' => 'Instalacion'],
                        ['img' => 'person-3.jpg', 'name' => 'Mantenimiento', 'tag' => 'Preventivo'],
                        ['img' => 'person-4.jpg', 'name' => 'Reparacion', 'tag' => 'Especialista'],
                        ['img' => 'person-5.jpg', 'name' => 'Configuracion', 'tag' => 'Software'],
                        ['img' => 'person-6.jpg', 'name' => 'Emergencias', 'tag' => 'Atencion'],
                    ] as $index => $provider)
                        <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="{{ 60 * $index }}">
                            <article class="provider-card">
                                <img src="{{ asset('assets/images/' . $provider['img']) }}" alt="{{ $provider['name'] }}">
                                <div class="provider-overlay">
                                    <span>{{ $provider['tag'] }}</span>
                                    <h5>{{ $provider['name'] }}</h5>
                                    <p><i class="ri-star-fill"></i> Informacion clara antes de solicitar.</p>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="reviews" class="section-padding reviews-section">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <span class="eyebrow dark">Confianza</span>
                    <h2>La confianza crece con cada servicio finalizado.</h2>
                    <p>Las reseñas ayudan a elegir mejor y motivan a los proveedores a cuidar cada atencion.</p>
                </div>

                <div class="row g-4">
                    @foreach ([
                        ['avatar' => 'avatar-1.jpg', 'name' => 'Gerardo Tapia', 'role' => 'Cliente', 'text' => 'Pude solicitar soporte con claridad y hacer seguimiento hasta la cita finalizada.'],
                        ['avatar' => 'avatar-2.jpg', 'name' => 'Manuel Zapana', 'role' => 'Cliente', 'text' => 'La plataforma me ayudo a encontrar un proveedor cercano y revisar su perfil antes de solicitar.'],
                        ['avatar' => 'avatar-4.jpg', 'name' => 'Oscar Paz', 'role' => 'Proveedor', 'text' => 'El perfil, portafolio y documentos ayudan a mostrar confianza desde el primer contacto.'],
                    ] as $review)
                        <div class="col-lg-4" data-aos="fade-up">
                            <article class="review-card">
                                <div class="stars">
                                    <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
                                </div>
                                <p>{{ $review['text'] }}</p>
                                <div class="review-person">
                                    <img src="{{ asset('assets/images/' . $review['avatar']) }}" alt="{{ $review['name'] }}">
                                    <div>
                                        <h6>{{ $review['name'] }}</h6>
                                        <span>{{ $review['role'] }}</span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="contact" class="cta-section">
            <div class="container">
                <div class="cta-panel" data-aos="zoom-in">
                    <span class="eyebrow">La Paz, Bolivia</span>
                    <h2>Convierte la busqueda de servicios en una experiencia ordenada, medible y confiable.</h2>
                    <p>Registrate para solicitar ayuda o para ofrecer tus servicios profesionales en La Paz.</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        @auth
                            <a href="{{ route('inicio') }}" class="btn btn-neon">Ir al panel</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-ghost">Ingresar</a>
                            <a href="{{ route('register') }}" class="btn btn-neon">Registrarse</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-auto">
                    <p class="mb-0">TecnoConexion conecta personas que necesitan ayuda con proveedores confiables.</p>
                </div>

                <div class="col-auto text-end">
                    <p class="mb-0">2026 Proyecto Integrador Isaac Alejandro Tola</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="{{ asset('assets/js/landing.js') }}"></script>
</body>

</html>
