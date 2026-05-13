<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mis especialidades</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->proveedorEspecialidades->count() }} registradas</span>
</div>

<div class="vstack gap-2">
    @forelse ($perfilProveedor->proveedorEspecialidades as $proveedorEspecialidad)
        <div class="border rounded p-3">
            <div class="d-flex justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">{{ $proveedorEspecialidad->especialidad?->nombre }}</div>
                    <small class="text-muted">
                        {{ $proveedorEspecialidad->especialidad?->rubroTipoServicio?->rubro?->nombre }}
                        /
                        {{ $proveedorEspecialidad->especialidad?->rubroTipoServicio?->tipoServicio?->nombre }}
                    </small>
                </div>
                <div class="text-end">
                    @if ($proveedorEspecialidad->es_principal)
                        <span class="badge bg-info-subtle text-info">Principal</span>
                    @endif
                    @if ($proveedorEspecialidad->estado)
                        <span class="badge bg-success-subtle text-success">Activa</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger">Inactiva</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            Aun no tienes especialidades registradas.
        </div>
    @endforelse
</div>
