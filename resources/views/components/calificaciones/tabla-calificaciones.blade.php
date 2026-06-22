<?php

use App\Models\Calificacion;
use App\Services\CalificacionService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $puntuacion = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarEliminarCalificacion' => 'eliminarCalificacion',
        'confirmarEstadoCalificacion' => 'actualizarEstadoCalificacion',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedPuntuacion(): void
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

    public function estadoBadge(string $estado): string
    {
        return match ($estado) {
            'visible' => 'bg-success-subtle text-success',
            'oculta' => 'bg-danger-subtle text-danger',
            'pendiente_revision' => 'bg-warning-subtle text-warning',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function estadoLabel(string $estado): string
    {
        return ucfirst(str_replace('_', ' ', $estado));
    }

    public function estrellas(int $puntuacion): string
    {
        return str_repeat('★', $puntuacion) . str_repeat('☆', 5 - $puntuacion);
    }

    public function actualizarEstadoCalificacion(int $calificacionId, string $estado): void
    {
        abort_unless(auth()->user()->can('ocultar calificaciones'), 403);

        $calificacion = Calificacion::findOrFail($calificacionId);

        app(CalificacionService::class)->actualizarEstado($calificacion, $estado);

        $this->dispatch('calificacion-actualizada', message: 'Estado de la calificación actualizado correctamente.');
    }

    public function eliminarCalificacion(int $calificacionId): void
    {
        abort_unless(auth()->user()->can('eliminar calificaciones'), 403);

        $calificacion = Calificacion::findOrFail($calificacionId);

        app(CalificacionService::class)->eliminar($calificacion);

        $this->dispatch('calificacion-actualizada', message: 'Calificación eliminada correctamente.');
    }

    public function with(): array
    {
        $allowedSorts = ['id', 'puntuacion', 'estado', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';

        $calificaciones = Calificacion::query()
            ->with([
                'cita.solicitud.cliente',
                'cita.solicitud.perfilProveedor',
                'cita.solicitud.especialidad',
                'detalles.aspecto',
                'respuesta.user',
            ])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('calificaciones.id', 'like', '%' . $this->search . '%')
                        ->orWhere('calificaciones.comentario', 'like', '%' . $this->search . '%')
                        ->orWhereHas('cita.solicitud.cliente', function ($clienteQuery) {
                            $clienteQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('cita.solicitud.perfilProveedor', function ($proveedorQuery) {
                            $proveedorQuery->where('nombre_publico', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('cita.solicitud.especialidad', function ($especialidadQuery) {
                            $especialidadQuery->where('nombre', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estado !== '', fn ($query) => $query->where('estado', $this->estado))
            ->when($this->puntuacion !== '', fn ($query) => $query->where('puntuacion', $this->puntuacion))
            ->orderBy('calificaciones.' . $sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'calificaciones' => $calificaciones,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="calificaciones-success-message" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" placeholder="Cliente, proveedor, especialidad o comentario" wire:model.live.debounce.300ms="search">
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="visible">Visibles</option>
                <option value="oculta">Ocultas</option>
                <option value="pendiente_revision">Pendiente revisión</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Puntuación</label>
            <select class="form-select" wire:model.live="puntuacion">
                <option value="">Todas</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'estrella' : 'estrellas' }}</option>
                @endfor
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
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0 calificaciones-table">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('id')">
                            ID <i class="{{ $this->sortIcon('id') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Cita</th>
                    <th>Cliente</th>
                    <th>Proveedor</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('puntuacion')">
                            Puntuación <i class="{{ $this->sortIcon('puntuacion') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Comentario</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado')">
                            Estado <i class="{{ $this->sortIcon('estado') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Respuesta</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('created_at')">
                            Fecha <i class="{{ $this->sortIcon('created_at') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($calificaciones as $calificacion)
                    <tr wire:key="calificacion-{{ $calificacion->id }}">
                        <td class="fw-semibold">#{{ $calificacion->id }}</td>
                        <td>
                            <div class="fw-semibold">Cita #{{ $calificacion->cita_id }}</div>
                            <small class="text-muted">{{ $calificacion->cita?->solicitud?->especialidad?->nombre ?? 'Sin especialidad' }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $calificacion->cita?->solicitud?->cliente?->name ?? 'Sin cliente' }}</div>
                            <small class="text-muted">{{ $calificacion->cita?->solicitud?->cliente?->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $calificacion->cita?->solicitud?->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                        </td>
                        <td>
                            <span class="calificacion-stars">{{ $this->estrellas((int) $calificacion->puntuacion) }}</span>
                            <span class="fw-semibold ms-1">{{ $calificacion->puntuacion }}.0</span>
                        </td>
                        <td>
                            <div class="calificacion-comment-cell">
                                {{ $calificacion->comentario ?: 'Sin comentario' }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $this->estadoBadge($calificacion->estado) }}">
                                {{ $this->estadoLabel($calificacion->estado) }}
                            </span>
                        </td>
                        <td>
                            @if ($calificacion->respuesta)
                                <span class="badge bg-info-subtle text-info">Respondida</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                            @endif
                        </td>
                        <td>{{ optional($calificacion->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('ocultar calificaciones')
                                    <button
                                        type="button"
                                        class="btn btn-sm {{ $calificacion->estado === 'visible' ? 'btn-soft-warning' : 'btn-soft-success' }} js-toggle-calificacion-livewire"
                                        title="{{ $calificacion->estado === 'visible' ? 'Ocultar' : 'Mostrar' }}"
                                        data-calificacion-id="{{ $calificacion->id }}"
                                        data-estado="{{ $calificacion->estado === 'visible' ? 'oculta' : 'visible' }}"
                                    >
                                        <i class="{{ $calificacion->estado === 'visible' ? 'ri-eye-off-line' : 'ri-eye-line' }} align-bottom"></i>
                                    </button>
                                @endcan

                                @can('eliminar calificaciones')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-soft-danger js-delete-calificacion-livewire"
                                        title="Eliminar"
                                        data-calificacion-id="{{ $calificacion->id }}"
                                    >
                                        <i class="ri-delete-bin-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            No se encontraron calificaciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $calificaciones->links() }}
    </div>
</div>
