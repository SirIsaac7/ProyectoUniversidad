<h5 class="mb-3">Mi ubicacion</h5>

@if ($perfilProveedor->ubicacion)
    <div class="border rounded p-3">
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Zona</small>
                <div class="fw-semibold">{{ $perfilProveedor->ubicacion->zona }}</div>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Radio de cobertura</small>
                <div class="fw-semibold">{{ $perfilProveedor->ubicacion->radio_cobertura_km }} km</div>
            </div>
            <div class="col-12">
                <small class="text-muted d-block">Direccion</small>
                <div>{{ $perfilProveedor->ubicacion->direccion }}</div>
            </div>
        </div>
    </div>
@else
    <div class="text-center text-muted py-4">
        Aun no tienes ubicacion registrada.
    </div>
@endif
