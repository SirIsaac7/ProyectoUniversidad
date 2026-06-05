<?php

use App\Models\HistorialSolicitud;
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

    public function estadoBadge(?string $estado): string
    {
        return match ($estado) {
            'aceptada', 'finalizada' => 'bg-success-subtle text-success',
            'rechazada', 'cancelada' => 'bg-danger-subtle text-danger',
            'en_proceso' => 'bg-info-subtle text-info',
            'pendiente' => 'bg-warning-subtle text-warning',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function estadoLabel(?string $estado): string
    {
        return $estado ? ucfirst(str_replace('_', ' ', $estado)) : 'Sin estado';
    }

    public function with(): array
    {
        $allowedSorts = ['id', 'estado_anterior', 'estado_nuevo', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';

        $historiales = HistorialSolicitud::query()
            ->with([
                'user:id,name,email',
                'solicitud.cliente',
                'solicitud.perfilProveedor',
            ])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('historial_solicitudes.id', 'like', '%' . $this->search . '%')
                        ->orWhere('historial_solicitudes.estado_anterior', 'like', '%' . $this->search . '%')
                        ->orWhere('historial_solicitudes.estado_nuevo', 'like', '%' . $this->search . '%')
                        ->orWhere('historial_solicitudes.comentario', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('solicitud', function ($solicitudQuery) {
                            $solicitudQuery->where('titulo', 'like', '%' . $this->search . '%')
                                ->orWhereHas('cliente', function ($clienteQuery) {
                                    $clienteQuery->where('name', 'like', '%' . $this->search . '%')
                                        ->orWhere('email', 'like', '%' . $this->search . '%');
                                })
                                ->orWhereHas('perfilProveedor', function ($proveedorQuery) {
                                    $proveedorQuery->where('nombre_publico', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('historial_solicitudes.estado_nuevo', $this->estado);
            })
            ->orderBy('historial_solicitudes.' . $sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'historiales' => $historiales,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="historial-solicitudes-success-message" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Solicitud, usuario, comentario o estado"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label">Estado nuevo</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aceptada">Aceptada</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
                <option value="en_proceso">En proceso</option>
                <option value="finalizada">Finalizada</option>
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
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0 historial-solicitudes-table">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('id')">
                            ID <i class="{{ $this->sortIcon('id') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Solicitud</th>
                    <th>Usuario</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado_anterior')">
                            Estado anterior <i class="{{ $this->sortIcon('estado_anterior') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado_nuevo')">
                            Estado nuevo <i class="{{ $this->sortIcon('estado_nuevo') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Comentario</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('created_at')">
                            Fecha <i class="{{ $this->sortIcon('created_at') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historiales as $historial)
                    <tr wire:key="historial-solicitud-{{ $historial->id }}">
                        <td class="fw-semibold">#{{ $historial->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $historial->solicitud?->titulo ?? 'Sin solicitud' }}</div>
                            <small class="text-muted">
                                Cliente: {{ $historial->solicitud?->cliente?->name ?? 'Sin cliente' }}
                                |
                                Proveedor: {{ $historial->solicitud?->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}
                            </small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $historial->user?->name ?? 'Sistema' }}</div>
                            <small class="text-muted">{{ $historial->user?->email }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $this->estadoBadge($historial->estado_anterior) }}">
                                {{ $this->estadoLabel($historial->estado_anterior) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $this->estadoBadge($historial->estado_nuevo) }}">
                                {{ $this->estadoLabel($historial->estado_nuevo) }}
                            </span>
                        </td>
                        <td>{{ $historial->comentario ?: 'Sin comentario' }}</td>
                        <td>{{ optional($historial->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No se encontraron movimientos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $historiales->links() }}
    </div>
</div>
