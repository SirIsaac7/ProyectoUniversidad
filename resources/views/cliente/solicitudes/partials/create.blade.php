@can('crear mis solicitudes')
    <div class="card solicitud-side-panel {{ ($mostrarPanel ?? false) ? '' : 'd-none' }}" id="solicitudCreatePanel">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="solicitud-panel-icon bg-primary-subtle text-primary">
                    <i class="ri-add-line"></i>
                </span>
                <div>
                    <h5 class="mb-1">Nueva solicitud</h5>
                    <small class="text-muted">Completa los datos para solicitar un servicio.</small>
                </div>
            </div>

            <form class="needs-validation" novalidate method="POST" action="{{ route('cliente.solicitudes.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <select name="perfil_proveedor_id" class="form-select js-perfil-proveedor-select @error('perfil_proveedor_id') is-invalid @enderror" required>
                        <option value="">Selecciona un proveedor</option>
                        @foreach ($perfilesProveedores as $perfilProveedor)
                            <option value="{{ $perfilProveedor->id }}" @selected(old('perfil_proveedor_id', $proveedorSeleccionadoId ?? null) == $perfilProveedor->id)>
                                {{ $perfilProveedor->nombre_publico }} - {{ $perfilProveedor->user?->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('perfil_proveedor_id')
                        <div class="invalid-feedback d-block js-perfil-proveedor-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback js-perfil-proveedor-feedback">Por favor selecciona un proveedor.</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Especialidad <span class="text-danger">*</span></label>
                    <select name="especialidad_id" class="form-select js-especialidad-select @error('especialidad_id') is-invalid @enderror" data-selected-value="{{ old('especialidad_id', $especialidadSeleccionadaId ?? null) }}" required>
                        <option value="">Selecciona una especialidad</option>
                        @foreach ($especialidades as $especialidad)
                            <option value="{{ $especialidad['id'] }}" data-perfiles="{{ implode(',', $especialidad['perfiles']) }}" @selected(old('especialidad_id', $especialidadSeleccionadaId ?? null) == $especialidad['id'])>
                                {{ $especialidad['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('especialidad_id')
                        <div class="invalid-feedback d-block js-especialidad-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback js-especialidad-feedback">Por favor selecciona una especialidad.</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Titulo <span class="text-danger">*</span></label>
                    <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" maxlength="255" required>
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor ingresa el titulo de la solicitud.</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de atencion <span class="text-danger">*</span></label>
                    <select name="tipo_atencion" class="form-select js-tipo-atencion @error('tipo_atencion') is-invalid @enderror" required>
                        @foreach ($tiposAtencion as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('tipo_atencion', 'mixto') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    <div class="form-text js-tipo-atencion-help"></div>
                    @error('tipo_atencion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripcion <span class="text-danger">*</span></label>
                    <textarea name="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror" maxlength="3000" required>{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Por favor describe tu necesidad.</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Direccion</label>
                        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion') }}" maxlength="255">
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Zona</label>
                        <input type="text" name="zona" class="form-control @error('zona') is-invalid @enderror" value="{{ old('zona') }}" maxlength="255">
                        @error('zona')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fecha solicitada</label>
                        <input type="date" name="fecha_solicitada" class="form-control @error('fecha_solicitada') is-invalid @enderror" value="{{ old('fecha_solicitada') }}">
                        @error('fecha_solicitada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hora solicitada</label>
                        <input type="time" name="hora_solicitada" class="form-control @error('hora_solicitada') is-invalid @enderror" value="{{ old('hora_solicitada') }}">
                        @error('hora_solicitada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror" maxlength="1000">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light js-solicitud-panel-cancel">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-bottom me-1"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
