<?php

use App\Models\ProveedorEspecialidad;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $principal = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioEstadoProveedorEspecialidad' => 'toggleEstado',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedPrincipal(): void
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

    public function toggleEstado(int $proveedorEspecialidadId): void
    {
        abort_unless(auth()->user()->can('eliminar especialidades proveedor'), 403);

        $proveedorEspecialidad = ProveedorEspecialidad::findOrFail($proveedorEspecialidadId);

        $proveedorEspecialidad->update([
            'estado' => ! $proveedorEspecialidad->estado,
        ]);

        $this->dispatch(
            'proveedor-especialidad-estado-cambiado',
            message: $proveedorEspecialidad->estado
                ? 'Especialidad del proveedor activada correctamente.'
                : 'Especialidad del proveedor inactivada correctamente.'
        );
    }

    public function with(): array
    {
        $proveedorEspecialidades = ProveedorEspecialidad::query()
            ->with([
                'perfilProveedor.user',
                'especialidad.rubroTipoServicio.rubro',
                'especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('proveedor_especialidad.id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('perfilProveedor', function ($perfilQuery) {
                            $perfilQuery->where('nombre_publico', 'like', '%' . $this->search . '%')
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
                $query->where('proveedor_especialidad.estado', $this->estado);
            })
            ->when($this->principal !== '', function ($query) {
                $query->where('proveedor_especialidad.es_principal', $this->principal);
            });

        if ($this->sortField === 'proveedor') {
            $proveedorEspecialidades
                ->leftJoin('perfiles_proveedores', 'proveedor_especialidad.perfil_proveedor_id', '=', 'perfiles_proveedores.id')
                ->select('proveedor_especialidad.*')
                ->orderBy('perfiles_proveedores.nombre_publico', $this->sortDirection);
        } elseif ($this->sortField === 'especialidad') {
            $proveedorEspecialidades
                ->leftJoin('especialidades', 'proveedor_especialidad.especialidad_id', '=', 'especialidades.id')
                ->select('proveedor_especialidad.*')
                ->orderBy('especialidades.nombre', $this->sortDirection);
        } else {
            $proveedorEspecialidades->orderBy('proveedor_especialidad.' . $this->sortField, $this->sortDirection);
        }

        $proveedorEspecialidades = $proveedorEspecialidades->paginate($this->perPage);

        return [
            'proveedorEspecialidades' => $proveedorEspecialidades,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="proveedor-especialidades-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="proveedor-especialidades-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Proveedor, usuario, rubro, tipo o especialidad"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-3">
            <label class="form-label">Principal</label>
            <select class="form-select" wire:model.live="principal">
                <option value="">Todos</option>
                <option value="1">Principales</option>
                <option value="0">No principales</option>
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
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('proveedor')">
                            Proveedor <i class="{{ $this->sortIcon('proveedor') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Rubro</th>
                    <th>Tipo de servicio</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('especialidad')">
                            Especialidad <i class="{{ $this->sortIcon('especialidad') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('es_principal')">
                            Principal <i class="{{ $this->sortIcon('es_principal') }} ms-1 small text-muted"></i>
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
                @forelse ($proveedorEspecialidades as $proveedorEspecialidad)
                    <tr>
                        <td>{{ $proveedorEspecialidad->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $proveedorEspecialidad->perfilProveedor?->nombre_publico ?? 'Sin proveedor' }}</div>
                            <small class="text-muted">{{ $proveedorEspecialidad->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>{{ $proveedorEspecialidad->especialidad?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro' }}</td>
                        <td>{{ $proveedorEspecialidad->especialidad?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo' }}</td>
                        <td class="fw-semibold">{{ $proveedorEspecialidad->especialidad?->nombre ?? 'Sin especialidad' }}</td>
                        <td>
                            @if ($proveedorEspecialidad->es_principal)
                                <span class="badge bg-primary-subtle text-primary">Principal</span>
                            @else
                                <span class="badge bg-light text-muted">No</span>
                            @endif
                        </td>
                        <td>
                            @if ($proveedorEspecialidad->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ optional($proveedorEspecialidad->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('editar especialidades proveedor')
                                    <a
                                        href="{{ route('proveedor-especialidades.edit', $proveedorEspecialidad->id) }}"
                                        class="btn btn-sm btn-soft-warning"
                                        title="Editar"
                                    >
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar especialidades proveedor')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-proveedor-especialidad-livewire {{ $proveedorEspecialidad->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $proveedorEspecialidad->estado ? 'Inactivar' : 'Activar' }}"
                                        data-proveedor-especialidad-id="{{ $proveedorEspecialidad->id }}"
                                        data-proveedor-nombre="{{ $proveedorEspecialidad->perfilProveedor?->nombre_publico }}"
                                        data-especialidad-nombre="{{ $proveedorEspecialidad->especialidad?->nombre }}"
                                        data-accion="{{ $proveedorEspecialidad->estado ? 'inactivar' : 'activar' }}"
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
                            No se encontraron especialidades asignadas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $proveedorEspecialidades->links() }}
    </div>
</div>
