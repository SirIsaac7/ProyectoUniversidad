<?php

use App\Services\Cliente\BusquedaServicioService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $q = '';
    public string $rubroId = '';
    public string $tipoServicioId = '';
    public bool $usarUbicacionActual = false;
    public string $latitud = '';
    public string $longitud = '';

    protected string $paginationTheme = 'bootstrap';

    public function mount(array $filtros = []): void
    {
        $this->q = trim($filtros['q'] ?? '');
        $this->rubroId = (string) ($filtros['rubro_id'] ?? '');
        $this->tipoServicioId = (string) ($filtros['tipo_servicio_id'] ?? '');
        $this->usarUbicacionActual = (bool) ($filtros['usar_ubicacion_actual'] ?? false);
        $this->latitud = (string) ($filtros['latitud'] ?? '');
        $this->longitud = (string) ($filtros['longitud'] ?? '');
    }

    public function seleccionarRubro(string $rubroId): void
    {
        $this->rubroId = $rubroId;
        $this->tipoServicioId = '';
        $this->resetPage();
    }

    public function seleccionarTipoServicio(string $tipoServicioId): void
    {
        $this->tipoServicioId = $tipoServicioId;
        $this->resetPage();
    }

    public function limpiarCatalogo(): void
    {
        $this->rubroId = '';
        $this->tipoServicioId = '';
        $this->resetPage();
    }

    public function with(): array
    {
        return app(BusquedaServicioService::class)->datosVista([
            'q' => $this->q,
            'rubro_id' => $this->rubroId,
            'tipo_servicio_id' => $this->tipoServicioId,
            'usar_ubicacion_actual' => $this->usarUbicacionActual,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
        ]);
    }
};

?>

<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Explora por categoria</h5>
        @if ($rubroId || $tipoServicioId)
            <button type="button" class="btn btn-sm btn-soft-secondary" wire:click="limpiarCatalogo">
                <i class="ri-refresh-line align-bottom me-1"></i>
                Limpiar filtros
            </button>
        @endif
    </div>

    <div class="busqueda-categorias-row mb-4">
        @foreach ($rubros as $rubro)
            <button
                type="button"
                wire:click="seleccionarRubro('{{ $rubro->id }}')"
                class="card busqueda-rubro-card text-center {{ (string) $rubroId === (string) $rubro->id ? 'is-active' : '' }}"
            >
                <span class="card-body">
                    <span class="busqueda-rubro-icon mx-auto mb-2">
                        @if ($rubro->imagen)
                            <img src="{{ asset($rubro->imagen) }}" alt="{{ $rubro->nombre }}">
                        @else
                            <i class="ri-apps-2-line"></i>
                        @endif
                    </span>
                    <span class="fw-semibold">{{ $rubro->nombre }}</span>
                </span>
            </button>
        @endforeach
    </div>

    @if ($rubroId)
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="mb-0">Explora por tipo de servicio</h5>
                <p class="text-muted mb-0 small">Elige una opcion para afinar los proveedores disponibles.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-soft-secondary busqueda-scroll-btn" data-busqueda-scroll-target="#busquedaTiposServicio" data-busqueda-scroll-direction="-1">
                    <i class="ri-arrow-left-s-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-soft-secondary busqueda-scroll-btn" data-busqueda-scroll-target="#busquedaTiposServicio" data-busqueda-scroll-direction="1">
                    <i class="ri-arrow-right-s-line"></i>
                </button>
            </div>
        </div>

        <div id="busquedaTiposServicio" class="busqueda-tipos-row mb-4">
            @forelse ($tiposServicio as $tipoServicio)
                <button
                    type="button"
                    wire:click="seleccionarTipoServicio('{{ $tipoServicio->id }}')"
                    class="card busqueda-tipo-card {{ (string) $tipoServicioId === (string) $tipoServicio->id ? 'is-active' : '' }}"
                >
                    <span class="card-body">
                        <span class="busqueda-tipo-icon">
                            <i class="ri-map-pin-2-line"></i>
                        </span>
                        <span>
                            <span class="fw-semibold d-block">{{ $tipoServicio->nombre }}</span>
                            <small class="text-muted">Ver proveedores</small>
                        </span>
                    </span>
                </button>
            @empty
                <div class="card">
                    <div class="card-body text-muted">
                        Esta categoria aun no tiene tipos de servicio activos.
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    @if ($tieneBusqueda)
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Proveedores encontrados para ti</h5>
            <span class="badge bg-primary-subtle text-primary">{{ $proveedores->total() }} resultados</span>
        </div>

        <div class="proveedores-resultados-grid proveedores-resultados-grid--cards" wire:loading.class="opacity-50">
            @forelse ($proveedores as $proveedor)
                <div
                    class="card proveedor-search-card proveedor-search-card-horizontal proveedor-search-card--reveal"
                    wire:key="proveedor-busqueda-{{ $proveedor['id'] }}"
                    style="--card-delay: {{ $loop->index * 70 }}ms;"
                >
                    <div class="proveedor-search-personal-photo">
                        @if ($proveedor['foto_personal'])
                            <img src="{{ $proveedor['foto_personal'] }}" alt="{{ $proveedor['nombre_persona'] }}">
                        @else
                            <span>{{ mb_substr($proveedor['nombre_persona'], 0, 1) }}</span>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-1">
                            <div class="min-w-0">
                                <h5 class="mb-1 text-truncate">{{ $proveedor['nombre_persona'] }}</h5>
                                <p class="text-muted mb-0 text-truncate">{{ $proveedor['nombre_publico'] }}</p>
                            </div>
                        </div>

                        <div class="proveedor-search-stars mb-2">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-half-fill"></i>
                            <span>4.5</span>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 text-muted small mb-3">
                            <span>
                                <i class="ri-map-pin-line align-bottom me-1"></i>
                                {{ $proveedor['zona'] }}
                            </span>
                        </div>

                        <button
                            type="button"
                            class="btn btn-soft-primary btn-lg w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#proveedorPerfilModal{{ $proveedor['id'] }}"
                            data-proveedor-modal-tab="perfil"
                        >
                            <i class="ri-user-search-line align-bottom me-1"></i>
                            Ver perfil
                        </button>
                    </div>
                </div>

                @include('cliente.busqueda-servicios.partials.modal-proveedor', ['proveedor' => $proveedor, 'tiposAtencion' => $tiposAtencion])
            @empty
                <div class="card proveedores-resultados-empty">
                    <div class="card-body text-center py-5">
                        <div class="avatar-md mx-auto mb-3">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                <i class="ri-search-eye-line fs-24"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">No encontramos proveedores</h5>
                        <p class="text-muted mb-0">Prueba con otro tipo de servicio, texto de busqueda o ubicacion.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $proveedores->links() }}
        </div>
    @endif
</div>
