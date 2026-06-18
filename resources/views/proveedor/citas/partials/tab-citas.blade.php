<div class="tab-pane fade {{ $tabActivo === 'citas' ? 'show active' : '' }}" id="proveedorCitasTab" role="tabpanel">
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
                                            <small class="text-muted d-block">Cliente</small>
                                            <span class="fw-semibold">{{ $citaVista['cliente'] }}</span>
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

                                    @if ($citaVista['puede_iniciar'] || $citaVista['puede_completar'] || $citaVista['puede_cancelar'])
                                            <div class="d-flex flex-wrap gap-2 justify-content-end mt-3 pt-3 border-top">
                                                @can('editar mis citas proveedor')
                                                    @if ($citaVista['puede_iniciar'])
                                                        <form method="POST" action="{{ route('proveedor.citas.estado', $citaVista['id']) }}" class="js-confirm-submit" data-confirm-title="Iniciar atencion" data-confirm-text="La cita pasara a estado en atencion.">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="estado" value="en_atencion">
                                                            <button type="submit" class="btn btn-sm btn-soft-info">
                                                                <i class="ri-play-line align-bottom me-1"></i>
                                                                Iniciar
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($citaVista['puede_completar'])
                                                        <form method="POST" action="{{ route('proveedor.citas.estado', $citaVista['id']) }}" class="js-confirm-submit" data-confirm-title="Completar atencion" data-confirm-text="La cita quedara completada y la solicitud se finalizara.">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="estado" value="completada">
                                                            <button type="submit" class="btn btn-sm btn-soft-success">
                                                                <i class="ri-checkbox-circle-line align-bottom me-1"></i>
                                                                Completar
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan

                                                @can('cancelar mis citas proveedor')
                                                    @if ($citaVista['puede_cancelar'])
                                                        <form method="POST" action="{{ route('proveedor.citas.destroy', $citaVista['id']) }}" class="js-confirm-submit" data-confirm-title="Cancelar cita" data-confirm-text="La cita quedara cancelada.">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-soft-danger">
                                                                <i class="ri-close-circle-line align-bottom me-1"></i>
                                                                Cancelar
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                    @endif
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
                    <p class="text-muted mb-0">Cuando programes una cita con un cliente, aparecera aqui.</p>
                </div>
            @endif
        </div>
    </div>
</div>
