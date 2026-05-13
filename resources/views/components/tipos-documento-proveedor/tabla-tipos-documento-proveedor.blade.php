<?php

use App\Models\TipoDocumentoProveedor;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $obligatorio = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioEstadoTipoDocumentoProveedor' => 'toggleEstado',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedObligatorio(): void
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

    public function toggleEstado(int $tipoDocumentoProveedorId): void
    {
        abort_unless(auth()->user()->can('eliminar tipos documento proveedor'), 403);

        $tipoDocumentoProveedor = TipoDocumentoProveedor::findOrFail($tipoDocumentoProveedorId);

        $tipoDocumentoProveedor->update([
            'estado' => ! $tipoDocumentoProveedor->estado,
        ]);

        $this->dispatch(
            'tipo-documento-proveedor-estado-cambiado',
            message: $tipoDocumentoProveedor->estado
                ? 'Tipo de documento activado correctamente.'
                : 'Tipo de documento inactivado correctamente.'
        );
    }

    public function with(): array
    {
        $tiposDocumentoProveedor = TipoDocumentoProveedor::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('id', 'like', '%' . $this->search . '%')
                        ->orWhere('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('estado', $this->estado);
            })
            ->when($this->obligatorio !== '', function ($query) {
                $query->where('obligatorio', $this->obligatorio);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'tiposDocumentoProveedor' => $tiposDocumentoProveedor,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="tipos-documento-proveedor-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="tipos-documento-proveedor-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" placeholder="ID, nombre o descripcion" wire:model.live.debounce.300ms="search">
        </div>

        <div class="col-md-3">
            <label class="form-label">Obligatorio</label>
            <select class="form-select" wire:model.live="obligatorio">
                <option value="">Todos</option>
                <option value="1">Obligatorios</option>
                <option value="0">Opcionales</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        <div class="col-md-2">
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
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('nombre')">
                            Nombre <i class="{{ $this->sortIcon('nombre') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Descripcion</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('obligatorio')">
                            Obligatorio <i class="{{ $this->sortIcon('obligatorio') }} ms-1 small text-muted"></i>
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
                @forelse ($tiposDocumentoProveedor as $tipoDocumentoProveedor)
                    <tr>
                        <td>{{ $tipoDocumentoProveedor->id }}</td>
                        <td class="fw-semibold">{{ $tipoDocumentoProveedor->nombre }}</td>
                        <td>
                            <div style="max-width: 420px; white-space: normal; line-height: 1.45;">
                                {{ $tipoDocumentoProveedor->descripcion ?: 'Sin descripcion' }}
                            </div>
                        </td>
                        <td>
                            @if ($tipoDocumentoProveedor->obligatorio)
                                <span class="badge bg-warning-subtle text-warning">Obligatorio</span>
                            @else
                                <span class="badge bg-info-subtle text-info">Opcional</span>
                            @endif
                        </td>
                        <td>
                            @if ($tipoDocumentoProveedor->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ optional($tipoDocumentoProveedor->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar tipos documento proveedor')
                                    <a href="{{ route('tipos-documento-proveedor.edit', $tipoDocumentoProveedor->id) }}" class="btn btn-sm btn-soft-warning" title="Editar">
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar tipos documento proveedor')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-tipo-documento-proveedor-livewire {{ $tipoDocumentoProveedor->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $tipoDocumentoProveedor->estado ? 'Inactivar' : 'Activar' }}"
                                        data-tipo-documento-proveedor-id="{{ $tipoDocumentoProveedor->id }}"
                                        data-tipo-documento-proveedor-nombre="{{ $tipoDocumentoProveedor->nombre }}"
                                        data-accion="{{ $tipoDocumentoProveedor->estado ? 'inactivar' : 'activar' }}"
                                    >
                                        <i class="ri-refresh-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No se encontraron tipos de documento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $tiposDocumentoProveedor->links() }}
    </div>
</div>
