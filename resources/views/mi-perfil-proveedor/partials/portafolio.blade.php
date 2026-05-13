<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mi portafolio</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->portafolio->count() }} trabajos</span>
</div>

<div class="row g-3">
    @forelse ($perfilProveedor->portafolio as $trabajo)
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                @if ($trabajo->imagenes->first())
                    <img src="{{ asset($trabajo->imagenes->first()->imagen) }}" alt="Trabajo realizado" class="img-fluid rounded mb-3" style="height: 140px; width: 100%; object-fit: cover;">
                @endif
                <h6 class="mb-1">{{ $trabajo->titulo }}</h6>
                <p class="text-muted mb-2">{{ str($trabajo->descripcion)->limit(110) }}</p>
                <small class="text-muted">{{ optional($trabajo->fecha_trabajo)->format('d/m/Y') ?: 'Sin fecha' }}</small>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-4">
                Aun no tienes trabajos en tu portafolio.
            </div>
        </div>
    @endforelse
</div>
