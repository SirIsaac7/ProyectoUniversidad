@php
    $coloresBusqueda = [
        'color_primario' => 'Principal',
        'color_secundario' => 'Secundario',
        'color_boton' => 'Botones',
        'color_fondo' => 'Fondo',
        'color_indicador' => 'Indicadores',
        'color_fase_completada' => 'Fases completadas',
        'color_fase_actual' => 'Fase actual',
        'color_informativo' => 'Mensajes informativos',
        'color_error' => 'Errores',
        'color_destacado' => 'Elementos destacados',
    ];
    $etiquetasFases = [
        'iniciando' => 'Iniciando',
        'construyendo_filtros' => 'Construyendo filtros',
        'generando_embedding' => 'Generando embedding',
        'buscando_qdrant' => 'Buscando en Qdrant',
        'reranking' => 'Reranking',
        'normalizando_puntuaciones' => 'Normalizando puntuaciones',
        'calculando_ranking_final' => 'Calculando ranking final',
        'preparando_resultados' => 'Preparando resultados',
        'completado' => 'Completado',
        'sin_resultados' => 'Sin resultados',
        'error' => 'Error',
    ];
@endphp

<section class="card mb-4" id="personalizacionBusqueda" aria-labelledby="personalizacionBusquedaTitulo">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h5 class="card-title mb-1" id="personalizacionBusquedaTitulo">Personalización de búsqueda</h5>
            <p class="text-muted mb-0">Los cambios se aplican solamente al buscador semántico de clientes.</p>
        </div>
        @can('restaurar personalizacion busqueda')
            <form method="POST" action="{{ route('motor-busqueda.personalizacion.reset') }}" data-restaurar-personalizacion>
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-soft-danger">
                    <i class="ri-restart-line align-bottom me-1"></i>Restaurar valores
                </button>
            </form>
        @endcan
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('motor-busqueda.personalizacion.update') }}" id="personalizacionBusquedaForm">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="accordion motor-customization-accordion" id="personalizacionAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#personalizacionColores">Colores</button>
                            </h2>
                            <div id="personalizacionColores" class="accordion-collapse collapse show" data-bs-parent="#personalizacionAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        @foreach ($coloresBusqueda as $campo => $etiqueta)
                                            <div class="col-sm-6">
                                                <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                                                <div class="motor-color-control">
                                                    <input
                                                        type="color"
                                                        class="form-control form-control-color @error($campo) is-invalid @enderror"
                                                        id="{{ $campo }}"
                                                        name="{{ $campo }}"
                                                        value="{{ old($campo, $personalizacionBusqueda->{$campo}) }}"
                                                        data-personalizacion-field="{{ $campo }}"
                                                    >
                                                    <code data-color-value="{{ $campo }}">{{ old($campo, $personalizacionBusqueda->{$campo}) }}</code>
                                                </div>
                                                @error($campo)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#personalizacionTipografia">Tipografía y forma</button>
                            </h2>
                            <div id="personalizacionTipografia" class="accordion-collapse collapse" data-bs-parent="#personalizacionAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="familiaTipografica">Familia tipográfica</label>
                                            <select class="form-select" id="familiaTipografica" name="familia_tipografica" data-personalizacion-field="familia_tipografica">
                                                @foreach ($familiasBusqueda as $clave => $familia)
                                                    <option value="{{ $clave }}" @selected(old('familia_tipografica', $personalizacionBusqueda->familia_tipografica) === $clave)>{{ ucfirst($clave) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="grosorTexto">Grosor del texto</label>
                                            <select class="form-select" id="grosorTexto" name="grosor_texto" data-personalizacion-field="grosor_texto">
                                                @foreach ([400 => 'Normal', 500 => 'Medio', 600 => 'Seminegrita', 700 => 'Negrita', 800 => 'Extra negrita'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected((int) old('grosor_texto', $personalizacionBusqueda->grosor_texto) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="tamanoTitulos">Títulos (px)</label>
                                            <input type="number" class="form-control" min="18" max="36" id="tamanoTitulos" name="tamano_titulos" value="{{ old('tamano_titulos', $personalizacionBusqueda->tamano_titulos) }}" data-personalizacion-field="tamano_titulos">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="tamanoMensajes">Mensajes (px)</label>
                                            <input type="number" class="form-control" min="12" max="20" id="tamanoMensajes" name="tamano_mensajes" value="{{ old('tamano_mensajes', $personalizacionBusqueda->tamano_mensajes) }}" data-personalizacion-field="tamano_mensajes">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="radioBordes">Esquinas (px)</label>
                                            <input type="number" class="form-control" min="0" max="24" id="radioBordes" name="radio_bordes" value="{{ old('radio_bordes', $personalizacionBusqueda->radio_bordes) }}" data-personalizacion-field="radio_bordes">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#personalizacionProcesamiento">Procesamiento y resultados</button>
                            </h2>
                            <div id="personalizacionProcesamiento" class="accordion-collapse collapse" data-bs-parent="#personalizacionAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Animación</label>
                                            <select class="form-select" name="animacion" data-personalizacion-field="animacion">
                                                @foreach (['spinner' => 'Spinner circular', 'puntos' => 'Puntos animados', 'barra' => 'Barra indeterminada', 'etapas' => 'Indicador por etapas', 'busqueda' => 'Animación de búsqueda'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('animacion', $personalizacionBusqueda->animacion) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Presentación de fases</label>
                                            <select class="form-select" name="presentacion_fases" data-personalizacion-field="presentacion_fases">
                                                @foreach (['actual' => 'Solo fase actual', 'lista' => 'Lista de etapas', 'linea' => 'Línea por etapas', 'animacion' => 'Animación y mensajes'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('presentacion_fases', $personalizacionBusqueda->presentacion_fases) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Diseño de tarjetas</label>
                                            <select class="form-select" name="diseno_tarjetas" data-personalizacion-field="diseno_tarjetas">
                                                @foreach (['clasica' => 'Clásica', 'compacta' => 'Compacta', 'destacada' => 'Destacada'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('diseno_tarjetas', $personalizacionBusqueda->diseno_tarjetas) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Espaciado</label>
                                            <select class="form-select" name="espaciado" data-personalizacion-field="espaciado">
                                                @foreach (['compacto' => 'Compacto', 'normal' => 'Normal', 'amplio' => 'Amplio'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('espaciado', $personalizacionBusqueda->espaciado) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Distribución</label>
                                            <select class="form-select" name="distribucion" data-personalizacion-field="distribucion">
                                                @foreach (['estandar' => 'Estándar', 'detallada' => 'Detallada'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('distribucion', $personalizacionBusqueda->distribucion) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Calificaciones</label>
                                            <select class="form-select" name="apariencia_calificacion" data-personalizacion-field="apariencia_calificacion">
                                                @foreach (['estrellas' => 'Estrellas', 'insignia' => 'Insignia', 'texto' => 'Texto'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('apariencia_calificacion', $personalizacionBusqueda->apariencia_calificacion) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Explicaciones</label>
                                            <select class="form-select" name="apariencia_explicacion" data-personalizacion-field="apariencia_explicacion">
                                                @foreach (['caja' => 'Caja informativa', 'texto' => 'Texto sencillo'] as $valor => $texto)
                                                    <option value="{{ $valor }}" @selected(old('apariencia_explicacion', $personalizacionBusqueda->apariencia_explicacion) === $valor)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#personalizacionMensajes">Mensajes por fase</button>
                            </h2>
                            <div id="personalizacionMensajes" class="accordion-collapse collapse" data-bs-parent="#personalizacionAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        @foreach ($fasesBusqueda as $fase)
                                            <div class="col-md-6">
                                                <label class="form-label" for="mensaje_{{ $fase }}">{{ $etiquetasFases[$fase] }}</label>
                                                <input
                                                    type="text"
                                                    class="form-control @error('mensajes_fases.'.$fase) is-invalid @enderror"
                                                    id="mensaje_{{ $fase }}"
                                                    name="mensajes_fases[{{ $fase }}]"
                                                    maxlength="160"
                                                    value="{{ old('mensajes_fases.'.$fase, $personalizacionBusqueda->mensajes_fases[$fase] ?? '') }}"
                                                    data-personalizacion-mensaje="{{ $fase }}"
                                                    required
                                                >
                                                @error('mensajes_fases.'.$fase)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('modificar personalizacion busqueda')
                        <button type="submit" class="btn btn-primary mt-4">
                            <i class="ri-save-line align-bottom me-1"></i>Guardar personalización
                        </button>
                    @endcan
                </div>

                <div class="col-xl-5">
                    <div class="motor-preview-sticky">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Vista previa interactiva</h6>
                            <span class="badge bg-warning-subtle text-warning">Datos de demostración</span>
                        </div>
                        <div
                            id="vistaPreviaBusqueda"
                            class="motor-search-preview is-card-{{ $personalizacionCliente['diseno_tarjetas'] }} is-spacing-{{ $personalizacionCliente['espaciado'] }}"
                            style="{{ $personalizacionCliente['estilo'] }}"
                        >
                            <div class="motor-preview-search">
                                <input type="text" value="Mi laptop se calienta demasiado" readonly aria-label="Consulta de demostración">
                                <button type="button">Buscar técnicos</button>
                            </div>
                            <div class="motor-preview-processing">
                                <div class="busqueda-animation" data-preview-animation></div>
                                <div>
                                    <strong data-preview-title>Analizando tu consulta</strong>
                                    <p data-preview-message>{{ $personalizacionCliente['mensajes_fases']['buscando_qdrant'] }}</p>
                                </div>
                            </div>
                            <div class="motor-preview-phases" data-preview-phases>
                                <span class="is-complete"><i class="ri-check-line"></i>Consulta recibida</span>
                                <span class="is-current"><i class="ri-loader-4-line"></i>Buscando proveedores</span>
                            </div>
                            <article class="motor-preview-provider">
                                <div class="motor-preview-avatar">TC</div>
                                <div class="flex-grow-1">
                                    <small>Proveedor de demostración</small>
                                    <h5>TecnoConexión Demo</h5>
                                    <div class="motor-preview-rating"><i class="ri-star-fill"></i> 4.8 (24)</div>
                                    <p class="motor-preview-explanation">Recomendado porque su especialidad coincide con el problema descrito.</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
