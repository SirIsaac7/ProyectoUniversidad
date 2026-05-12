<?php

use App\Models\UbicacionProveedor;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarEliminarUbicacionProveedor' => 'eliminar',
    ];

    public function updatedSearch(): void
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

    public function eliminar(int $ubicacionProveedorId): void
    {
        abort_unless(auth()->user()->can('eliminar ubicaciones proveedor'), 403);

        UbicacionProveedor::findOrFail($ubicacionProveedorId)->delete();

        $this->dispatch('ubicacion-proveedor-eliminada', message: 'Ubicacion eliminada correctamente.');
    }

    public function with(): array
    {
        $ubicacionesProveedor = UbicacionProveedor::query()
            ->with('perfilProveedor.user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('ubicaciones_proveedor.id', 'like', '%' . $this->search . '%')
                        ->orWhere('ubicaciones_proveedor.zona', 'like', '%' . $this->search . '%')
                        ->orWhere('ubicaciones_proveedor.direccion', 'like', '%' . $this->search . '%')
                        ->orWhereHas('perfilProveedor', function ($perfilQuery) {
                            $perfilQuery->where('nombre_publico', 'like', '%' . $this->search . '%')
                                ->orWhereHas('user', function ($userQuery) {
                                    $userQuery->where('name', 'like', '%' . $this->search . '%')
                                        ->orWhere('email', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            });

        if ($this->sortField === 'proveedor') {
            $ubicacionesProveedor
                ->leftJoin('perfiles_proveedores', 'ubicaciones_proveedor.perfil_proveedor_id', '=', 'perfiles_proveedores.id')
                ->select('ubicaciones_proveedor.*')
                ->orderBy('perfiles_proveedores.nombre_publico', $this->sortDirection);
        } else {
            $ubicacionesProveedor->orderBy('ubicaciones_proveedor.' . $this->sortField, $this->sortDirection);
        }

        return [
            'ubicacionesProveedor' => $ubicacionesProveedor->paginate($this->perPage),
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="ubicaciones-proveedor-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="ubicaciones-proveedor-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Proveedor, correo, zona o direccion"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label">Registros</label>
            <select class="form-select" wire:model.live="perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="ubicaciones-index-map mb-3" id="ubicacionesProveedorIndexMap">
        @foreach ($ubicacionesProveedor as $ubicacionProveedor)
            @if ($ubicacionProveedor->latitud && $ubicacionProveedor->longitud)
                <span
                    class="d-none js-ubicacion-map-marker"
                    data-proveedor="{{ $ubicacionProveedor->perfilProveedor?->nombre_publico }}"
                    data-zona="{{ $ubicacionProveedor->zona }}"
                    data-lat="{{ $ubicacionProveedor->latitud }}"
                    data-lng="{{ $ubicacionProveedor->longitud }}"
                    data-radio="{{ $ubicacionProveedor->radio_cobertura_km }}"
                ></span>
            @endif
        @endforeach
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
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('proveedor')">
                            Proveedor <i class="{{ $this->sortIcon('proveedor') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('zona')">
                            Zona <i class="{{ $this->sortIcon('zona') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Direccion</th>
                    <th>Coordenadas</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('radio_cobertura_km')">
                            Radio <i class="{{ $this->sortIcon('radio_cobertura_km') }} ms-1 small text-muted"></i>
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
                @forelse ($ubicacionesProveedor as $ubicacionProveedor)
                    <tr>
                        <td>{{ $ubicacionProveedor->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $ubicacionProveedor->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                            <small class="text-muted">{{ $ubicacionProveedor->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>{{ $ubicacionProveedor->zona ?: 'Sin zona' }}</td>
                        <td>
                            <div style="max-width: 300px; white-space: normal;">
                                {{ $ubicacionProveedor->direccion ?: 'Sin direccion' }}
                            </div>
                        </td>
                        <td>
                            <div>{{ $ubicacionProveedor->latitud }}</div>
                            <div>{{ $ubicacionProveedor->longitud }}</div>
                        </td>
                        <td>{{ $ubicacionProveedor->radio_cobertura_km ? $ubicacionProveedor->radio_cobertura_km . ' km' : 'Sin radio' }}</td>
                        <td>{{ optional($ubicacionProveedor->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar ubicaciones proveedor')
                                    <a href="{{ route('ubicaciones-proveedor.edit', $ubicacionProveedor->id) }}" class="btn btn-sm btn-soft-warning" title="Editar">
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar ubicaciones proveedor')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-soft-danger js-delete-ubicacion-proveedor-livewire"
                                        title="Eliminar"
                                        data-ubicacion-proveedor-id="{{ $ubicacionProveedor->id }}"
                                        data-proveedor-nombre="{{ $ubicacionProveedor->perfilProveedor?->nombre_publico }}"
                                    >
                                        <i class="ri-delete-bin-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No se encontraron ubicaciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $ubicacionesProveedor->links() }}
    </div>
</div>
