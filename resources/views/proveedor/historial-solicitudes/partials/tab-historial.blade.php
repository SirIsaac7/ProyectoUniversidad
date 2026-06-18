<div class="tab-pane fade {{ $tabActivo === 'historial' ? 'show active' : '' }}" id="proveedorHistorialTab" role="tabpanel">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Historial recibido</h5>
            <span class="badge bg-info-subtle text-info">{{ method_exists($historiales, 'total') ? $historiales->total() : $historiales->count() }} movimientos</span>
        </div>
        <div class="card-body">
            @if ($historiales->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Movimiento</th>
                                <th>Usuario</th>
                                <th>Comentario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historialesVista as $historialVista)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $historialVista['solicitud'] }}</div>
                                        <small class="text-muted">{{ $historialVista['cliente'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $historialVista['estado_anterior'] }}</span>
                                        <i class="ri-arrow-right-line mx-1 text-muted"></i>
                                        <span class="badge bg-primary-subtle text-primary">{{ $historialVista['estado_nuevo'] }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $historialVista['usuario'] }}</div>
                                        <small class="text-muted">{{ $historialVista['email'] }}</small>
                                    </td>
                                    <td>{{ $historialVista['comentario'] }}</td>
                                    <td>{{ $historialVista['fecha'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $historiales->appends(['tab' => 'historial'])->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title rounded-circle bg-info-subtle text-info">
                            <i class="ri-history-line fs-24"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">Aun no hay historial</h5>
                    <p class="text-muted mb-0">Los cambios de las solicitudes recibidas apareceran aqui.</p>
                </div>
            @endif
        </div>
    </div>
</div>
