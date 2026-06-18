@php
    $panelAbierto = (string) old('solicitud_id') === (string) $solicitudVista['id'];
@endphp

<div class="card solicitud-side-panel sticky-xl-top {{ $panelAbierto ? '' : 'd-none' }}" id="citaCreatePanel{{ $solicitudVista['id'] }}">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h5 class="mb-1">Aceptar y agendar</h5>
                <p class="text-muted mb-0">Programa la atencion para {{ $solicitudVista['cliente'] }}.</p>
            </div>
            <span class="solicitud-panel-icon bg-success-subtle text-success">
                <i class="ri-calendar-check-line"></i>
            </span>
        </div>

        <form method="POST" action="{{ route('proveedor.citas.store') }}" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="solicitud_id" value="{{ $solicitudVista['id'] }}">

            @if ($errors->has('solicitud_id') && $panelAbierto)
                <div class="alert alert-danger bg-danger-subtle border-danger-subtle text-danger">
                    {{ $errors->first('solicitud_id') }}
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Fecha de atencion <span class="text-danger">*</span></label>
                <input type="date" name="fecha_cita" class="form-control @if($panelAbierto && $errors->has('fecha_cita')) is-invalid @endif" value="{{ $panelAbierto ? old('fecha_cita') : $solicitudVista['modelo']->fecha_solicitada?->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}" required>
                @if ($panelAbierto)
                    <div class="invalid-feedback d-block">{{ $errors->first('fecha_cita') }}</div>
                @endif
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label">Hora inicio <span class="text-danger">*</span></label>
                    <input type="time" name="hora_inicio" class="form-control @if($panelAbierto && $errors->has('hora_inicio')) is-invalid @endif" value="{{ $panelAbierto ? old('hora_inicio') : $solicitudVista['modelo']->hora_solicitada?->format('H:i') }}" required>
                    @if ($panelAbierto)
                        <div class="invalid-feedback d-block">{{ $errors->first('hora_inicio') }}</div>
                    @endif
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Hora fin <span class="text-danger">*</span></label>
                    <input type="time" name="hora_fin" class="form-control @if($panelAbierto && $errors->has('hora_fin')) is-invalid @endif" value="{{ $panelAbierto ? old('hora_fin') : '' }}" required>
                    @if ($panelAbierto)
                        <div class="invalid-feedback d-block">{{ $errors->first('hora_fin') }}</div>
                    @endif
                </div>
            </div>

            <div class="alert alert-info bg-info-subtle border-info-subtle text-info mt-3">
                <i class="ri-information-line me-1"></i>
                La cita debe estar dentro de tus horarios disponibles y no cruzarse con otra cita.
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" rows="3" class="form-control @if($panelAbierto && $errors->has('observaciones')) is-invalid @endif" placeholder="Indicaciones para la atencion">{{ $panelAbierto ? old('observaciones') : '' }}</textarea>
                @if ($panelAbierto)
                    <div class="invalid-feedback d-block">{{ $errors->first('observaciones') }}</div>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-soft-secondary js-solicitud-panel-cancel">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-calendar-check-line align-bottom me-1"></i>
                    Guardar cita
                </button>
            </div>
        </form>
    </div>
</div>
