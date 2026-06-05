<?php

use App\Models\Cita;
use App\Services\CitaService;
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
        'confirmarCancelacionCita' => 'cancelarCita',
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

    public function estadoBadge(string $estado): string
    {
        return match ($estado) {
            'programada', 'reprogramada' => 'bg-primary-subtle text-primary',
            'en_camino' => 'bg-info-subtle text-info',
            'en_atencion' => 'bg-warning-subtle text-warning',
            'completada' => 'bg-success-subtle text-success',
            'cancelada', 'no_asistio' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function estadoLabel(string $estado): string
    {
        return ucfirst(str_replace('_', ' ', $estado));
    }

    public function cancelarCita(int $citaId): void
    {
        abort_unless(auth()->user()->can('eliminar citas'), 403);

        $cita = Cita::findOrFail($citaId);

        if (in_array($cita->estado, ['completada', 'cancelada'], true)) {
            $this->dispatch('cita-cancelada', message: 'La cita ya no permite cancelacion.');
            return;
        }

        app(CitaService::class)->cancelar($cita, 'Cita cancelada desde administracion');

        $this->dispatch('cita-cancelada', message: 'Cita cancelada correctamente.');
    }

    public function with(): array
    {
        $allowedSorts = ['id', 'fecha_cita', 'hora_inicio', 'estado', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';

        $citas = Cita::query()
            ->with([
                'solicitud.cliente',
                'solicitud.perfilProveedor.user',
                'solicitud.especialidad.rubroTipoServicio.rubro',
                'solicitud.especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('citas.id', 'like', '%' . $this->search . '%')
                        ->orWhere('citas.estado', 'like', '%' . $this->search . '%')
                        ->orWhere('citas.observaciones', 'like', '%' . $this->search . '%')
                        ->orWhereHas('solicitud', function ($solicitudQuery) {
                            $solicitudQuery->where('titulo', 'like', '%' . $this->search . '%')
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
                                    $especialidadQuery->where('nombre', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('citas.estado', $this->estado);
            })
            ->orderBy('citas.' . $sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'citas' => $citas,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="citas-success-message" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Cliente, proveedor, solicitud o especialidad"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="programada">Programadas</option>
                <option value="reprogramada">Reprogramadas</option>
                <option value="en_camino">En camino</option>
                <option value="en_atencion">En atencion</option>
                <option value="completada">Completadas</option>
                <option value="cancelada">Canceladas</option>
                <option value="no_asistio">No asistio</option>
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
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0 citas-table">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('id')">
                            ID <i class="{{ $this->sortIcon('id') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Solicitud</th>
                    <th>Cliente</th>
                    <th>Proveedor</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('fecha_cita')">
                            Fecha <i class="{{ $this->sortIcon('fecha_cita') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Horario</th>
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
                @forelse ($citas as $cita)
                    <tr wire:key="cita-{{ $cita->id }}">
                        <td class="fw-semibold">#{{ $cita->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $cita->solicitud?->titulo ?? 'Sin solicitud' }}</div>
                            <small class="text-muted">
                                {{ $cita->solicitud?->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro' }}
                                -
                                {{ $cita->solicitud?->especialidad?->nombre ?? 'Sin especialidad' }}
                            </small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $cita->solicitud?->cliente?->name ?? 'Sin cliente' }}</div>
                            <small class="text-muted">{{ $cita->solicitud?->cliente?->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $cita->solicitud?->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                            <small class="text-muted">{{ $cita->solicitud?->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>{{ $cita->fecha_cita?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                        <td>
                            <div>{{ $cita->hora_inicio?->format('H:i') ?? '--:--' }} - {{ $cita->hora_fin?->format('H:i') ?? '--:--' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $this->estadoBadge($cita->estado) }}">
                                {{ $this->estadoLabel($cita->estado) }}
                            </span>
                        </td>
                        <td>{{ optional($cita->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('eliminar citas')
                                    @if (! in_array($cita->estado, ['completada', 'cancelada'], true))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-soft-danger js-cancelar-cita-livewire"
                                            title="Cancelar cita"
                                            data-cita-id="{{ $cita->id }}"
                                            data-solicitud-titulo="{{ $cita->solicitud?->titulo }}"
                                            data-cliente-nombre="{{ $cita->solicitud?->cliente?->name }}"
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
                            No se encontraron citas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $citas->links() }}
    </div>
</div>
