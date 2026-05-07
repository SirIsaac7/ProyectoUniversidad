<?php

use App\Models\Rubro;
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
        'confirmarCambioEstadoRubro' => 'toggleEstado',
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
            return '↕';
        }

        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }

    public function toggleEstado(int $rubroId): void
    {
        abort_unless(auth()->user()->can('eliminar rubros'), 403);

        $rubro = Rubro::findOrFail($rubroId);

        $rubro->update([
            'estado' => ! $rubro->estado,
        ]);

        $this->dispatch(
            'rubro-estado-cambiado',
            message: $rubro->estado
                ? 'Rubro activado correctamente.'
                : 'Rubro inactivado correctamente.'
        );
    }

    public function with(): array
    {
        $rubros = Rubro::query()
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'rubros' => $rubros,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="rubros-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="rubros-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="ID, nombre o descripcion"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-4">
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
                            ID <span class="ms-1 small text-muted">{{ $this->sortIcon('id') }}</span>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('nombre')">
                            Nombre <span class="ms-1 small text-muted">{{ $this->sortIcon('nombre') }}</span>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('descripcion')">
                            Descripcion <span class="ms-1 small text-muted">{{ $this->sortIcon('descripcion') }}</span>
                        </button>
                    </th>
                    <th>Imagen</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado')">
                            Estado <span class="ms-1 small text-muted">{{ $this->sortIcon('estado') }}</span>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('created_at')">
                            Fecha de creacion <span class="ms-1 small text-muted">{{ $this->sortIcon('created_at') }}</span>
                        </button>
                    </th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rubros as $rubro)
                    <tr>
                        <td>{{ $rubro->id }}</td>
                        <td class="fw-semibold">{{ $rubro->nombre }}</td>
                        <td>
                            <div style="max-width: 420px; white-space: normal; line-height: 1.45;">
                                {{ $rubro->descripcion ?: 'Sin descripcion' }}
                            </div>
                        </td>
                        <td>
                            @if ($rubro->imagen)
                                <img
                                    src="{{ asset($rubro->imagen) }}"
                                    alt="{{ $rubro->nombre }}"
                                    class="rounded border"
                                    style="width: 56px; height: 56px; object-fit: cover;"
                                >
                            @else
                                <span class="text-muted">Sin imagen</span>
                            @endif
                        </td>
                        <td>
                            @if ($rubro->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ optional($rubro->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar rubros')
                                <a
                                    href="{{ route('rubros.edit', $rubro->id) }}"
                                    class="btn btn-sm btn-soft-warning"
                                    title="Editar"
                                >
                                    <i class="ri-pencil-fill align-bottom"></i>
                                </a>
                                @endcan

                                @can('eliminar rubros')
                                <button
                                    type="button"
                                    class="btn btn-sm js-toggle-rubro-livewire {{ $rubro->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                    title="{{ $rubro->estado ? 'Inactivar' : 'Activar' }}"
                                    data-rubro-id="{{ $rubro->id }}"
                                    data-rubro-nombre="{{ $rubro->nombre }}"
                                    data-accion="{{ $rubro->estado ? 'inactivar' : 'activar' }}"
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
                            No se encontraron rubros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $rubros->links() }}
    </div>
</div>
