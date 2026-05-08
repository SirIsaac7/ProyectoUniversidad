<?php

use App\Models\HorarioProveedor;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dia = '';
    public string $disponible = '';
    public string $tipoAtencion = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioDisponibilidadHorarioProveedor' => 'toggleDisponible',
    ];

    public array $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDia(): void
    {
        $this->resetPage();
    }

    public function updatedDisponible(): void
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

    public function toggleDisponible(int $horarioProveedorId): void
    {
        abort_unless(auth()->user()->can('eliminar horarios proveedor'), 403);

        $horarioProveedor = HorarioProveedor::findOrFail($horarioProveedorId);

        $horarioProveedor->update([
            'disponible' => ! $horarioProveedor->disponible,
        ]);

        $this->dispatch(
            'horario-proveedor-disponibilidad-cambiada',
            message: $horarioProveedor->disponible
                ? 'Horario activado correctamente.'
                : 'Horario inactivado correctamente.'
        );
    }

    public function formatHora($hora): string
    {
        if (! $hora) {
            return '--:--';
        }

        return is_string($hora) ? substr($hora, 0, 5) : $hora->format('H:i');
    }

    public function with(): array
    {
        $horariosProveedor = HorarioProveedor::query()
            ->with('perfilProveedor.user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('horarios_proveedor.id', 'like', '%' . $this->search . '%')
                        ->orWhere('horarios_proveedor.tipo_atencion', 'like', '%' . $this->search . '%')
                        ->orWhereHas('perfilProveedor', function ($perfilQuery) {
                            $perfilQuery->where('nombre_publico', 'like', '%' . $this->search . '%')
                                ->orWhereHas('user', function ($userQuery) {
                                    $userQuery->where('name', 'like', '%' . $this->search . '%')
                                        ->orWhere('email', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->dia !== '', function ($query) {
                $query->where('horarios_proveedor.dia_semana', $this->dia);
            })
            ->when($this->disponible !== '', function ($query) {
                $query->where('horarios_proveedor.disponible', $this->disponible);
            })
            ->when($this->tipoAtencion !== '', function ($query) {
                $query->where('horarios_proveedor.tipo_atencion', $this->tipoAtencion);
            });

        if ($this->sortField === 'proveedor') {
            $horariosProveedor
                ->leftJoin('perfiles_proveedores', 'horarios_proveedor.perfil_proveedor_id', '=', 'perfiles_proveedores.id')
                ->select('horarios_proveedor.*')
                ->orderBy('perfiles_proveedores.nombre_publico', $this->sortDirection);
        } else {
            $horariosProveedor->orderBy('horarios_proveedor.' . $this->sortField, $this->sortDirection);
        }

        return [
            'horariosProveedor' => $horariosProveedor->paginate($this->perPage),
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="horarios-proveedor-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="horarios-proveedor-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Proveedor, correo o tipo"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-2">
            <label class="form-label">Dia</label>
            <select class="form-select" wire:model.live="dia">
                <option value="">Todos</option>
                @foreach ($this->dias as $numero => $nombre)
                    <option value="{{ $numero }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Tipo</label>
            <select class="form-select" wire:model.live="tipoAtencion">
                <option value="">Todos</option>
                <option value="mixto">Mixto</option>
                <option value="domicilio">Domicilio</option>
                <option value="local">Local</option>
                <option value="remoto">Remoto</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Disponibilidad</label>
            <select class="form-select" wire:model.live="disponible">
                <option value="">Todos</option>
                <option value="1">Disponibles</option>
                <option value="0">No disponibles</option>
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
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('proveedor')">
                            Proveedor <i class="{{ $this->sortIcon('proveedor') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('dia_semana')">
                            Dia <i class="{{ $this->sortIcon('dia_semana') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('hora_inicio')">
                            Horario <i class="{{ $this->sortIcon('hora_inicio') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('tipo_atencion')">
                            Tipo de atencion <i class="{{ $this->sortIcon('tipo_atencion') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('disponible')">
                            Disponible <i class="{{ $this->sortIcon('disponible') }} ms-1 small text-muted"></i>
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
                @forelse ($horariosProveedor as $horarioProveedor)
                    <tr>
                        <td>{{ $horarioProveedor->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $horarioProveedor->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                            <small class="text-muted">{{ $horarioProveedor->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>{{ $this->dias[$horarioProveedor->dia_semana] ?? 'Sin dia' }}</td>
                        <td class="fw-semibold">
                            {{ $this->formatHora($horarioProveedor->hora_inicio) }} - {{ $this->formatHora($horarioProveedor->hora_fin) }}
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info text-capitalize">{{ $horarioProveedor->tipo_atencion }}</span>
                        </td>
                        <td>
                            @if ($horarioProveedor->disponible)
                                <span class="badge bg-success-subtle text-success">Disponible</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">No disponible</span>
                            @endif
                        </td>
                        <td>{{ optional($horarioProveedor->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar horarios proveedor')
                                    <a
                                        href="{{ route('horarios-proveedor.edit', $horarioProveedor->id) }}"
                                        class="btn btn-sm btn-soft-warning"
                                        title="Editar"
                                    >
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar horarios proveedor')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-horario-proveedor-livewire {{ $horarioProveedor->disponible ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $horarioProveedor->disponible ? 'Inactivar' : 'Activar' }}"
                                        data-horario-proveedor-id="{{ $horarioProveedor->id }}"
                                        data-proveedor-nombre="{{ $horarioProveedor->perfilProveedor?->nombre_publico }}"
                                        data-dia="{{ $this->dias[$horarioProveedor->dia_semana] ?? 'este dia' }}"
                                        data-horario="{{ $this->formatHora($horarioProveedor->hora_inicio) }} - {{ $this->formatHora($horarioProveedor->hora_fin) }}"
                                        data-accion="{{ $horarioProveedor->disponible ? 'inactivar' : 'activar' }}"
                                    >
                                        <i class="ri-refresh-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No se encontraron horarios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $horariosProveedor->links() }}
    </div>
</div>
