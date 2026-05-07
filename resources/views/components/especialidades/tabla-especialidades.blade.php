<?php

use App\Models\Especialidad;
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
        'confirmarCambioEstadoEspecialidad' => 'toggleEstado',
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

    public function toggleEstado(int $especialidadId): void
    {
        abort_unless(auth()->user()->can('eliminar especialidades'), 403);

        $especialidad = Especialidad::findOrFail($especialidadId);

        $especialidad->update([
            'estado' => ! $especialidad->estado,
        ]);

        $this->dispatch(
            'especialidad-estado-cambiado',
            message: $especialidad->estado
                ? 'Especialidad activada correctamente.'
                : 'Especialidad inactivada correctamente.'
        );
    }

    public function with(): array
    {
        $especialidades = Especialidad::query()
            ->with('rubroTipoServicio.rubro', 'rubroTipoServicio.tipoServicio')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('especialidades.id', 'like', '%' . $this->search . '%')
                        ->orWhere('especialidades.nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('especialidades.descripcion', 'like', '%' . $this->search . '%')
                        ->orWhereHas('rubroTipoServicio.rubro', function ($rubroQuery) {
                            $rubroQuery->where('nombre', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('rubroTipoServicio.tipoServicio', function ($tipoServicioQuery) {
                            $tipoServicioQuery->where('nombre', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('especialidades.estado', $this->estado);
            });

        if ($this->sortField === 'rubro') {
            $especialidades
                ->leftJoin('rubro_tipo_servicio', 'especialidades.rubro_tipo_servicio_id', '=', 'rubro_tipo_servicio.id')
                ->leftJoin('rubros', 'rubro_tipo_servicio.rubro_id', '=', 'rubros.id')
                ->select('especialidades.*')
                ->orderBy('rubros.nombre', $this->sortDirection);
        } elseif ($this->sortField === 'tipo_servicio') {
            $especialidades
                ->leftJoin('rubro_tipo_servicio', 'especialidades.rubro_tipo_servicio_id', '=', 'rubro_tipo_servicio.id')
                ->leftJoin('tipos_servicio', 'rubro_tipo_servicio.tipo_servicio_id', '=', 'tipos_servicio.id')
                ->select('especialidades.*')
                ->orderBy('tipos_servicio.nombre', $this->sortDirection);
        } else {
            $especialidades->orderBy('especialidades.' . $this->sortField, $this->sortDirection);
        }

        $especialidades = $especialidades->paginate($this->perPage);

        return [
            'especialidades' => $especialidades,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="especialidades-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="especialidades-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="ID, rubro, tipo, nombre o descripcion"
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
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('rubro')">
                            Rubro <span class="ms-1 small text-muted">{{ $this->sortIcon('rubro') }}</span>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('tipo_servicio')">
                            Tipo de servicio <span class="ms-1 small text-muted">{{ $this->sortIcon('tipo_servicio') }}</span>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('nombre')">
                            Especialidad <span class="ms-1 small text-muted">{{ $this->sortIcon('nombre') }}</span>
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
                @forelse ($especialidades as $especialidad)
                    <tr>
                        <td>{{ $especialidad->id }}</td>
                        <td>{{ optional($especialidad->rubroTipoServicio?->rubro)->nombre ?? 'Sin rubro' }}</td>
                        <td>{{ optional($especialidad->rubroTipoServicio?->tipoServicio)->nombre ?? 'Sin tipo' }}</td>
                        <td class="fw-semibold">{{ $especialidad->nombre }}</td>
                        <td>
                            <div style="max-width: 360px; white-space: normal; line-height: 1.45;">
                                {{ $especialidad->descripcion ?: 'Sin descripcion' }}
                            </div>
                        </td>
                        <td>
                            @if ($especialidad->imagen)
                                <img
                                    src="{{ asset($especialidad->imagen) }}"
                                    alt="{{ $especialidad->nombre }}"
                                    class="rounded border"
                                    style="width: 56px; height: 56px; object-fit: cover;"
                                >
                            @else
                                <span class="text-muted">Sin imagen</span>
                            @endif
                        </td>
                        <td>
                            @if ($especialidad->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ optional($especialidad->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar especialidades')
                                    <a
                                        href="{{ route('especialidades.edit', $especialidad->id) }}"
                                        class="btn btn-sm btn-soft-warning"
                                        title="Editar"
                                    >
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar especialidades')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-especialidad-livewire {{ $especialidad->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $especialidad->estado ? 'Inactivar' : 'Activar' }}"
                                        data-especialidad-id="{{ $especialidad->id }}"
                                        data-especialidad-nombre="{{ $especialidad->nombre }}"
                                        data-accion="{{ $especialidad->estado ? 'inactivar' : 'activar' }}"
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
                            No se encontraron especialidades.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $especialidades->links() }}
    </div>
</div>
