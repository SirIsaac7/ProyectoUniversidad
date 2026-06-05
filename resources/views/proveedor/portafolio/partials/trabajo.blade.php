@php
    $imagenPrincipal = $trabajo->imagenes->where('estado', true)->first();
    $estaActivo = (bool) $trabajo->estado;
@endphp

<div
    class="mi-portafolio-card {{ $estaActivo ? '' : 'is-inactive' }}"
    data-portafolio-card
    data-portafolio-status="{{ $estaActivo ? 'activo' : 'inactivo' }}"
    data-search-text="{{ strtolower($trabajo->titulo . ' ' . $trabajo->descripcion . ' ' . optional($trabajo->fecha_trabajo)->format('d/m/Y')) }}"
>
    <div class="mi-portafolio-card-image">
        @if ($imagenPrincipal)
            <img src="{{ asset($imagenPrincipal->imagen) }}" alt="{{ $trabajo->titulo }}">
        @else
            <div class="mi-portafolio-empty-image">
                <i class="ri-image-line"></i>
            </div>
        @endif

        <span class="badge {{ $estaActivo ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
            {{ $trabajo->imagenes->where('estado', true)->count() }} imagenes
        </span>

        @unless ($estaActivo)
            <span class="badge bg-danger-subtle text-danger mi-portafolio-status-badge">Inactivo</span>
        @endunless
    </div>

    <div class="mi-portafolio-card-body">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="min-w-0">
                <h5 class="mb-2">{{ $trabajo->titulo }}</h5>
                <p class="text-muted mb-3">{{ $trabajo->descripcion ?: 'Sin descripcion registrada.' }}</p>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2">
            <small class="text-muted">
                <i class="ri-calendar-line align-bottom me-1"></i>
                {{ optional($trabajo->fecha_trabajo)->format('d/m/Y') ?: 'Sin fecha' }}
            </small>

            <div class="mi-portafolio-actions">
                @if ($imagenPrincipal)
                    <a href="{{ asset($imagenPrincipal->imagen) }}" target="_blank" rel="noopener" class="btn btn-sm mi-portafolio-view-btn" title="Ver imagen">
                        <i class="ri-eye-line"></i>
                    </a>
                @endif

                @can('gestionar portafolio proveedor')
                    <button type="button" class="btn btn-sm mi-portafolio-edit-btn js-mi-portafolio-panel-toggle" data-panel-target="miPortafolioEditPanel{{ $trabajo->id }}" title="Editar">
                        <i class="ri-pencil-line"></i>
                    </button>

                    @if ($estaActivo)
                        <form method="POST" action="{{ route('mi-perfil-proveedor.portafolio.destroy', $trabajo->id) }}" class="js-confirm-delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm mi-portafolio-delete-btn" title="Inactivar">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('mi-perfil-proveedor.portafolio.activar', $trabajo->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm mi-portafolio-activate-btn" title="Activar">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>
