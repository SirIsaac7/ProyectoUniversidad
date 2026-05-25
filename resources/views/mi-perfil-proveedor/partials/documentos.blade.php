<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mis documentos</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->documentos->count() }} cargados</span>
</div>

@can('gestionar documentos proveedor')
    <form class="needs-validation border rounded p-3 mb-3" novalidate method="POST" action="{{ route('mi-perfil-proveedor.documentos.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                <select name="tipo_documento_proveedor_id" class="form-select @error('tipo_documento_proveedor_id') is-invalid @enderror" required>
                    <option value="">Selecciona un documento</option>
                    @foreach ($tiposDocumentoDisponibles as $tipoDocumento)
                        <option value="{{ $tipoDocumento->id }}" @selected(old('tipo_documento_proveedor_id') == $tipoDocumento->id)>
                            {{ $tipoDocumento->nombre }} {{ $tipoDocumento->obligatorio ? '(Obligatorio)' : '(Opcional)' }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_documento_proveedor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona el tipo de documento.</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Archivo <span class="text-danger">*</span></label>
                <input type="file" name="archivo" class="form-control @error('archivo') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                @error('archivo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Por favor selecciona un archivo.</div>
                @enderror
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Subir</button>
            </div>
        </div>
    </form>
@endcan

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Revision</th>
                <th>Archivo</th>
                @can('gestionar documentos proveedor') <th>Actualizar</th> @endcan
                <th>Observacion</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perfilProveedor->documentos as $documento)
                <tr>
                    <td>{{ $documento->tipoDocumentoProveedor?->nombre }}</td>
                    <td>
                        @if ($documento->estado_revision === 'aprobado')
                            <span class="badge bg-success-subtle text-success">Aprobado</span>
                        @elseif ($documento->estado_revision === 'rechazado')
                            <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                        @endif
                    </td>
                    <td><a href="{{ asset($documento->archivo) }}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-info">Ver</a></td>
                    @can('gestionar documentos proveedor')
                        <td style="min-width: 360px;">
                            <form class="needs-validation d-flex gap-2" novalidate method="POST" action="{{ route('mi-perfil-proveedor.documentos.update', $documento->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <select name="tipo_documento_proveedor_id" class="form-select form-select-sm @error('tipo_documento_proveedor_id') is-invalid @enderror" required>
                                    @foreach ($tiposDocumentoDisponibles as $tipoDocumento)
                                        <option value="{{ $tipoDocumento->id }}" @selected($documento->tipo_documento_proveedor_id === $tipoDocumento->id)>{{ $tipoDocumento->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_documento_proveedor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <input type="file" name="archivo" class="form-control form-control-sm @error('archivo') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                @error('archivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-sm btn-soft-primary">Guardar</button>
                            </form>
                            <form method="POST" action="{{ route('mi-perfil-proveedor.documentos.destroy', $documento->id) }}" class="js-confirm-delete-form mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger">Eliminar</button>
                            </form>
                        </td>
                    @endcan
                    <td>{{ $documento->observacion ?: 'Sin observacion' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Aun no tienes documentos cargados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
