@php
    $tipoSeleccionado = old('tipo', $reporte?->tipo ?? 'resumen_general');
    $opcionesGuardadas = old('opciones', $reporte?->opciones ?? []);
    $opcionesPorTipo = $opcionesPorTipo ?? config('reportes.opciones', []);
@endphp

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Datos del reporte</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $reporte?->nombre) }}"
                        placeholder="Ej: Reporte mensual de proveedores">
                    @error('nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de reporte <span class="text-danger">*</span></label>
                    <select name="tipo" id="tipo_reporte" class="form-select @error('tipo') is-invalid @enderror">
                        @foreach ($tipos as $valor => $texto)
                            <option value="{{ $valor }}" @selected($tipoSeleccionado === $valor)>
                                {{ $texto }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror"
                            value="{{ old('fecha_inicio', $reporte?->fecha_inicio?->format('Y-m-d')) }}">
                        @error('fecha_inicio')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" name="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror"
                            value="{{ old('fecha_fin', $reporte?->fecha_fin?->format('Y-m-d')) }}">
                        @error('fecha_fin')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Opciones dinamicas</h5>
            </div>
            <div class="card-body">
                @foreach ($opcionesPorTipo as $tipo => $configuracion)
                    <div class="reporte-opciones" data-reporte-opciones="{{ $tipo }}">
                        <div class="row g-3">
                            @foreach (($configuracion['selects'] ?? []) as $clave => $select)
                                <div class="col-md-6">
                                    <label class="form-label">{{ $select['label'] }}</label>
                                    <select name="opciones[{{ $clave }}]" class="form-select">
                                        @foreach ($select['opciones'] as $valor => $texto)
                                            <option value="{{ $valor }}" @selected((string) data_get($opcionesGuardadas, $clave, array_key_first($select['opciones'])) === (string) $valor)>
                                                {{ $texto }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach

                            @foreach (($configuracion['switches'] ?? []) as $clave => $texto)
                                <div class="col-md-6">
                                    <input type="hidden" name="opciones[{{ $clave }}]" value="0">
                                    <div class="form-check form-switch reporte-switch">
                                        <input class="form-check-input" type="checkbox" name="opciones[{{ $clave }}]" value="1"
                                            id="opcion_{{ $tipo }}_{{ $clave }}"
                                            @checked((bool) data_get($opcionesGuardadas, $clave, true))>
                                        <label class="form-check-label" for="opcion_{{ $tipo }}_{{ $clave }}">
                                            {{ $texto }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (empty($configuracion['selects']) && empty($configuracion['switches']))
                            <div class="alert alert-info mb-0">
                                Este tipo de reporte no requiere filtros adicionales.
                            </div>
                        @endif
                    </div>
                @endforeach

                @error('opciones')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Contenido</h5>
            </div>
            <div class="card-body">
                <input type="hidden" name="incluir_graficas" value="0">
                <input type="hidden" name="incluir_imagenes" value="0">

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="incluir_graficas" value="1" id="incluir_graficas"
                        @checked(old('incluir_graficas', $reporte?->incluir_graficas ?? true))>
                    <label class="form-check-label" for="incluir_graficas">Incluir graficas HTML</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="incluir_imagenes" value="1" id="incluir_imagenes"
                        @checked(old('incluir_imagenes', $reporte?->incluir_imagenes ?? true))>
                    <label class="form-check-label" for="incluir_imagenes">Incluir imagenes disponibles</label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                        <option value="1" @selected((string) old('estado', (int) ($reporte?->estado ?? true)) === '1')>Activo</option>
                        <option value="0" @selected((string) old('estado', (int) ($reporte?->estado ?? true)) === '0')>Inactivo</option>
                    </select>
                    @error('estado')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @can('configurar reportes')
                    <div class="alert alert-info mb-0">
                        <i class="ri-file-settings-line me-1"></i>
                        La apariencia del PDF se configura una sola vez para todos los reportes.
                        <a href="{{ route('reportes.configuracion') }}" class="fw-semibold">Ir a configuracion general</a>
                    </div>
                @else
                    <div class="alert alert-light mb-0">
                        <i class="ri-information-line me-1"></i>
                        Este formulario solo define filtros y contenido. El diseno del PDF es global.
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('reportes.index') }}" class="btn btn-soft-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">
        <i class="ri-save-3-line align-bottom me-1"></i>
        {{ $submitText }}
    </button>
</div>
