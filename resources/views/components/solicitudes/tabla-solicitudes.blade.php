<?php

use App\Models\Solicitud;
use App\Services\SolicitudService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $tipoAtencion = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCancelacionSolicitud' => 'cancelarSolicitud',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedTipoAtencion(): void
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
            'aceptada' => 'bg-success-subtle text-success',
            'rechazada', 'cancelada' => 'bg-danger-subtle text-danger',
            'en_proceso' => 'bg-info-subtle text-info',
            'finalizada' => 'bg-primary-subtle text-primary',
            default => 'bg-warning-subtle text-warning',
        };
    }

    public function tipoAtencionLabel(string $tipoAtencion): string
    {
        return match ($tipoAtencion) {
            'domicilio' => 'Domicilio',
            'local' => 'En local',
            'remoto' => 'Remoto',
            default => 'Mixto',
        };
    }

    public function cancelarSolicitud(int $solicitudId): void
    {
        abort_unless(auth()->user()->can('eliminar solicitudes'), 403);

        $solicitud = Solicitud::findOrFail($solicitudId);

        if (in_array($solicitud->estado, ['cancelada', 'finalizada'], true)) {
            $this->dispatch('solicitud-cancelada', message: 'La solicitud ya no permite cancelacion.');
            return;
        }

        app(SolicitudService::class)->cancelar($solicitud);

        $this->dispatch('solicitud-cancelada', message: 'Solicitud cancelada correctamente.');
    }

    public function with(): array
    {
        $allowedSorts = ['id', 'tipo_atencion', 'estado', 'fecha_solicitada', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';

        $solicitudes = Solicitud::query()
            ->with([
                'cliente',
                'perfilProveedor.user',
                'especialidad.rubroTipoServicio.rubro',
                'especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('solicitudes.id', 'like', '%' . $this->search . '%')
                        ->orWhere('solicitudes.titulo', 'like', '%' . $this->search . '%')
                        ->orWhere('solicitudes.descripcion', 'like', '%' . $this->search . '%')
                        ->orWhere('solicitudes.estado', 'like', '%' . $this->search . '%')
                        ->orWhereHas('cliente', function ($clienteQuery) {
                            $clienteQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('perfilProveedor', function ($proveedorQuery) {
                            $proveedorQuery->where('nombre_publico', 'like', '%' . $this->search . '%')
                                ->orWhereHas('user', function ($userQuery) {
                                    $userQuery->where('name', 'like', '%' . $this->search . '%')
                                        ->orWhere('email', 'like', '%' . $this->search . '%');
                                });
                        })
                        ->orWhereHas('especialidad', function ($especialidadQuery) {
                            $especialidadQuery->where('nombre', 'like', '%' . $this->search . '%')
                                ->orWhereHas('rubroTipoServicio.rubro', function ($rubroQuery) {
                                    $rubroQuery->where('nombre', 'like', '%' . $this->search . '%');
                                })
                                ->orWhereHas('rubroTipoServicio.tipoServicio', function ($tipoQuery) {
                                    $tipoQuery->where('nombre', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('solicitudes.estado', $this->estado);
            })
            ->when($this->tipoAtencion !== '', function ($query) {
                $query->where('solicitudes.tipo_atencion', $this->tipoAtencion);
            })
            ->orderBy('solicitudes.' . $sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'solicitudes' => $solicitudes,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="solicitudes-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="solicitudes-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Cliente, proveedor, especialidad o estado"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-3">
            <label class="form-label">Tipo de atencion</label>
            <select class="form-select" wire:model.live="tipoAtencion">
                <option value="">Todos</option>
                <option value="mixto">Mixto</option>
                <option value="domicilio">Domicilio</option>
                <option value="local">En local</option>
                <option value="remoto">Remoto</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="pendiente">Pendientes</option>
                <option value="aceptada">Aceptadas</option>
                <option value="rechazada">Rechazadas</option>
                <option value="cancelada">Canceladas</option>
                <option value="en_proceso">En proceso</option>
                <option value="finalizada">Finalizadas</option>
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
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0 solicitudes-table">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('id')">
                            ID <i class="{{ $this->sortIcon('id') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Cliente</th>
                    <th>Proveedor</th>
                    <th>Especialidad</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('tipo_atencion')">
                            Tipo <i class="{{ $this->sortIcon('tipo_atencion') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('fecha_solicitada')">
                            Fecha solicitada <i class="{{ $this->sortIcon('fecha_solicitada') }} ms-1 small text-muted"></i>
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
                @forelse ($solicitudes as $solicitud)
                    <tr wire:key="solicitud-{{ $solicitud->id }}">
                        <td>
                            <div class="fw-semibold">#{{ $solicitud->id }}</div>
                            <small class="text-muted">{{ $solicitud->titulo }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $solicitud->cliente?->name ?? 'Sin cliente' }}</div>
                            <small class="text-muted">{{ $solicitud->cliente?->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $solicitud->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                            <small class="text-muted">{{ $solicitud->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $solicitud->especialidad?->nombre ?? 'Sin especialidad' }}</div>
                            <small class="text-muted">
                                {{ $solicitud->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro' }}
                                -
                                {{ $solicitud->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo' }}
                            </small>
                        </td>
                        <td>{{ $this->tipoAtencionLabel($solicitud->tipo_atencion) }}</td>
                        <td>
                            @if ($solicitud->fecha_solicitada)
                                <div>{{ $solicitud->fecha_solicitada->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $solicitud->hora_solicitada?->format('H:i') ?: 'Sin hora' }}</small>
                            @else
                                <span class="text-muted">Sin fecha</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $this->estadoBadge($solicitud->estado) }}">
                                {{ str_replace('_', ' ', ucfirst($solicitud->estado)) }}
                            </span>
                        </td>
                        <td>{{ optional($solicitud->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('eliminar solicitudes')
                                    @if (! in_array($solicitud->estado, ['cancelada', 'finalizada'], true))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-soft-danger js-cancelar-solicitud-livewire"
                                            title="Cancelar solicitud"
                                            data-solicitud-id="{{ $solicitud->id }}"
                                            data-titulo-solicitud="{{ $solicitud->titulo }}"
                                            data-cliente-nombre="{{ $solicitud->cliente?->name }}"
                                        >
                                            <i class="ri-close-circle-line align-bottom"></i>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-soft-secondary"
                                            title="Sin accion disponible"
                                            disabled
                                        >
                                            <i class="ri-forbid-2-line align-bottom"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No se encontraron solicitudes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $solicitudes->links() }}
    </div>
</div>
