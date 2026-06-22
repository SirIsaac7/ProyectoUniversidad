<?php

use App\Models\AspectoCalificacion;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $sortField = 'orden';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioEstadoAspectoCalificacion' => 'toggleEstado',
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

    public function toggleEstado(int $aspectoCalificacionId): void
    {
        abort_unless(auth()->user()->can('eliminar aspectos calificacion'), 403);

        $aspectoCalificacion = AspectoCalificacion::findOrFail($aspectoCalificacionId);

        $aspectoCalificacion->update([
            'estado' => ! $aspectoCalificacion->estado,
        ]);

        $this->dispatch(
            'aspecto-calificacion-estado-cambiado',
            message: $aspectoCalificacion->estado
                ? 'Aspecto activado correctamente.'
                : 'Aspecto inactivado correctamente.'
        );
    }

    public function with(): array
    {
        $allowedSorts = ['id', 'nombre', 'estado', 'orden', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'orden';

        return [
            'aspectosCalificacion' => AspectoCalificacion::query()
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->estado !== '', fn ($query) => $query->where('estado', $this->estado))
                ->orderBy($sortField, $this->sortDirection)
                ->paginate($this->perPage),
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="aspectos-calificacion-success-message" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" placeholder="Nombre o descripción" wire:model.live.debounce.300ms="search">
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
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('orden')">
                            Orden <i class="{{ $this->sortIcon('orden') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('nombre')">
                            Nombre <i class="{{ $this->sortIcon('nombre') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Descripción</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado')">
                            Estado <i class="{{ $this->sortIcon('estado') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aspectosCalificacion as $aspectoCalificacion)
                    <tr wire:key="aspecto-calificacion-{{ $aspectoCalificacion->id }}">
                        <td class="fw-semibold">{{ $aspectoCalificacion->orden }}</td>
                        <td class="fw-semibold">{{ $aspectoCalificacion->nombre }}</td>
                        <td>
                            <div style="max-width: 460px; white-space: normal;">
                                {{ $aspectoCalificacion->descripcion ?: 'Sin descripción' }}
                            </div>
                        </td>
                        <td>
                            @if ($aspectoCalificacion->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar aspectos calificacion')
                                    <a href="{{ route('aspectos-calificacion.edit', $aspectoCalificacion->id) }}" class="btn btn-sm btn-soft-warning" title="Editar">
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar aspectos calificacion')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-aspecto-calificacion-livewire {{ $aspectoCalificacion->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $aspectoCalificacion->estado ? 'Inactivar' : 'Activar' }}"
                                        data-aspecto-calificacion-id="{{ $aspectoCalificacion->id }}"
                                        data-aspecto-calificacion-nombre="{{ $aspectoCalificacion->nombre }}"
                                        data-accion="{{ $aspectoCalificacion->estado ? 'inactivar' : 'activar' }}"
                                    >
                                        <i class="ri-refresh-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No se encontraron aspectos de calificación.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $aspectosCalificacion->links() }}
    </div>
</div>
