<?php

use App\Models\PortafolioProveedor;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioEstadoPortafolioProveedor' => 'toggleEstado',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function sortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'ri-expand-up-down-line';
        }

        return $this->sortDirection === 'asc' ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line';
    }

    public function toggleEstado(int $portafolioProveedorId): void
    {
        abort_unless(auth()->user()->can('eliminar portafolio proveedor'), 403);

        $portafolioProveedor = PortafolioProveedor::findOrFail($portafolioProveedorId);

        $portafolioProveedor->update([
            'estado' => ! $portafolioProveedor->estado,
        ]);

        $this->dispatch(
            'portafolio-proveedor-estado-cambiado',
            message: $portafolioProveedor->estado
                ? 'Trabajo de portafolio activado correctamente.'
                : 'Trabajo de portafolio inactivado correctamente.'
        );
    }

    public function with(): array
    {
        $portafolioProveedor = PortafolioProveedor::query()
            ->with(['perfilProveedor.user', 'imagenes'])
            ->withCount('imagenes')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('portafolio_proveedor.id', 'like', '%' . $this->search . '%')
                        ->orWhere('portafolio_proveedor.titulo', 'like', '%' . $this->search . '%')
                        ->orWhere('portafolio_proveedor.descripcion', 'like', '%' . $this->search . '%')
                        ->orWhereHas('perfilProveedor', function ($perfilQuery) {
                            $perfilQuery->where('nombre_publico', 'like', '%' . $this->search . '%')
                                ->orWhereHas('user', function ($userQuery) {
                                    $userQuery->where('name', 'like', '%' . $this->search . '%')
                                        ->orWhere('email', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('portafolio_proveedor.estado', $this->estado);
            });

        if ($this->sortField === 'proveedor') {
            $portafolioProveedor
                ->leftJoin('perfiles_proveedores', 'portafolio_proveedor.perfil_proveedor_id', '=', 'perfiles_proveedores.id')
                ->select('portafolio_proveedor.*')
                ->orderBy('perfiles_proveedores.nombre_publico', $this->sortDirection);
        } elseif ($this->sortField === 'imagenes') {
            $portafolioProveedor->orderBy('imagenes_count', $this->sortDirection);
        } else {
            $portafolioProveedor->orderBy('portafolio_proveedor.' . $this->sortField, $this->sortDirection);
        }

        return [
            'portafolioProveedor' => $portafolioProveedor->paginate($this->perPage),
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="portafolio-proveedor-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="portafolio-proveedor-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Proveedor, correo, titulo o descripcion"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Registros</label>
            <select class="form-select" wire:model.live="perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('id')">
                            ID <i class="{{ $this->sortIcon('id') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Imagen</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('proveedor')">
                            Proveedor <i class="{{ $this->sortIcon('proveedor') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('titulo')">
                            Trabajo <i class="{{ $this->sortIcon('titulo') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('fecha_trabajo')">
                            Fecha del trabajo <i class="{{ $this->sortIcon('fecha_trabajo') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('imagenes')">
                            Imagenes <i class="{{ $this->sortIcon('imagenes') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado')">
                            Estado <i class="{{ $this->sortIcon('estado') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('created_at')">
                            Fecha de creacion <i class="{{ $this->sortIcon('created_at') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($portafolioProveedor as $trabajoPortafolio)
                    @php
                        $imagenPrincipal = $trabajoPortafolio->imagenes->firstWhere('estado', true) ?? $trabajoPortafolio->imagenes->first();
                    @endphp
                    <tr>
                        <td>{{ $trabajoPortafolio->id }}</td>
                        <td>
                            @if ($imagenPrincipal)
                                <img
                                    src="{{ asset($imagenPrincipal->imagen) }}"
                                    alt="{{ $imagenPrincipal->titulo ?? $trabajoPortafolio->titulo }}"
                                    class="rounded border"
                                    style="width: 72px; height: 54px; object-fit: cover;"
                                >
                            @else
                                <span class="badge bg-light text-muted">Sin imagen</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $trabajoPortafolio->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                            <small class="text-muted">{{ $trabajoPortafolio->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $trabajoPortafolio->titulo }}</div>
                            <small class="text-muted d-block" style="max-width: 260px; white-space: normal;">
                                {{ str($trabajoPortafolio->descripcion)->limit(80) ?: 'Sin descripcion' }}
                            </small>
                        </td>
                        <td>{{ optional($trabajoPortafolio->fecha_trabajo)->format('d/m/Y') ?? 'Sin fecha' }}</td>
                        <td>
                            <span class="badge bg-info-subtle text-info">
                                {{ $trabajoPortafolio->imagenes_count }} imagen(es)
                            </span>
                        </td>
                        <td>
                            @if ($trabajoPortafolio->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ optional($trabajoPortafolio->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar portafolio proveedor')
                                    <a
                                        href="{{ route('portafolio-proveedor.edit', $trabajoPortafolio->id) }}"
                                        class="btn btn-sm btn-soft-warning"
                                        title="Editar"
                                    >
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar portafolio proveedor')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-portafolio-proveedor-livewire {{ $trabajoPortafolio->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $trabajoPortafolio->estado ? 'Inactivar' : 'Activar' }}"
                                        data-portafolio-proveedor-id="{{ $trabajoPortafolio->id }}"
                                        data-titulo="{{ $trabajoPortafolio->titulo }}"
                                        data-accion="{{ $trabajoPortafolio->estado ? 'inactivar' : 'activar' }}"
                                    >
                                        <i class="ri-refresh-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No se encontraron trabajos de portafolio.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $portafolioProveedor->links() }}
    </div>
</div>
