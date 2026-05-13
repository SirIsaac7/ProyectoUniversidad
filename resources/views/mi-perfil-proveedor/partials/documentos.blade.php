<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mis documentos</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->documentos->count() }} cargados</span>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Revision</th>
                <th>Archivo</th>
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
                    <td>
                        <a href="{{ asset($documento->archivo) }}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-info">
                            Ver
                        </a>
                    </td>
                    <td>{{ $documento->observacion ?: 'Sin observacion' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Aun no tienes documentos cargados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
