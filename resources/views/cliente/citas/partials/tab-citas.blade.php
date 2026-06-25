<div class="tab-pane fade {{ $tabActivo === 'citas' ? 'show active' : '' }}" id="clienteCitasTab" role="tabpanel">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Mis citas</h5>
            <span class="badge bg-primary-subtle text-primary">{{ method_exists($citas, 'total') ? $citas->total() : $citas->count() }} registros</span>
        </div>
        <div class="card-body">
            @if ($citas->count())
                <div class="row g-3">
                    @foreach ($citasVista as $citaVista)
                        <div class="col-lg-6">
                            <div class="card border h-100 mb-0 solicitud-client-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                        <div>
                                            <h5 class="mb-1">{{ $citaVista['titulo'] }}</h5>
                                            <p class="text-muted mb-0">{{ $citaVista['rubro'] }} - {{ $citaVista['especialidad'] }}</p>
                                        </div>
                                        <span class="badge bg-{{ $citaVista['meta']['class'] }}-subtle text-{{ $citaVista['meta']['class'] }}">
                                            <i class="{{ $citaVista['meta']['icon'] }} me-1"></i>
                                            {{ $citaVista['meta']['label'] }}
                                        </span>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Proveedor</small>
                                            <span class="d-flex align-items-center gap-2 fw-semibold">
                                                <span class="avatar-xs">
                                                    @if ($citaVista['proveedor_avatar'])
                                                        <img src="{{ $citaVista['proveedor_avatar'] }}" alt="{{ $citaVista['proveedor'] }}" class="rounded-circle img-fluid">
                                                    @else
                                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                            {{ $citaVista['proveedor_inicial'] }}
                                                        </span>
                                                    @endif
                                                </span>
                                                {{ $citaVista['proveedor'] }}
                                            </span>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Fecha</small>
                                            <span class="fw-semibold">{{ $citaVista['fecha'] }}</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Horario</small>
                                            <span class="fw-semibold">
                                                {{ $citaVista['hora_inicio'] }}
                                                -
                                                {{ $citaVista['hora_fin'] }}
                                            </span>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">Observaciones</small>
                                            <span class="fw-semibold">{{ $citaVista['observaciones'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $citas->appends(['tab' => 'citas'])->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                            <i class="ri-calendar-check-line fs-24"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">Aun no tienes citas</h5>
                    <p class="text-muted mb-0">Cuando un proveedor programe una cita, aparecera aqui.</p>
                </div>
            @endif
        </div>
    </div>
</div>
