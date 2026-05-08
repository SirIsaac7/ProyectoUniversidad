<?php

use App\Models\PerfilProveedor;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $estadoVerificacion = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public ?int $perfilProveedorDetalleId = null;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioEstadoPerfilProveedor' => 'toggleEstado',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedEstadoVerificacion(): void
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

    public function toggleEstado(int $perfilProveedorId): void
    {
        abort_unless(auth()->user()->can('eliminar proveedores'), 403);

        $perfilProveedor = PerfilProveedor::findOrFail($perfilProveedorId);

        $perfilProveedor->update([
            'estado' => ! $perfilProveedor->estado,
        ]);

        $this->dispatch(
            'perfil-proveedor-estado-cambiado',
            message: $perfilProveedor->estado
                ? 'Proveedor activado correctamente.'
                : 'Proveedor inactivado correctamente.'
        );
    }

    public function verDetalle(int $perfilProveedorId): void
    {
        abort_unless(auth()->user()->can('ver detalle proveedores'), 403);

        $this->perfilProveedorDetalleId = $perfilProveedorId;

        $this->dispatch('abrir-modal-detalle-proveedor');
    }

    public function getPerfilProveedorDetalleProperty(): ?PerfilProveedor
    {
        if (! $this->perfilProveedorDetalleId) {
            return null;
        }

        return PerfilProveedor::with('user.roles')->find($this->perfilProveedorDetalleId);
    }

    public function with(): array
    {
        $perfilesProveedores = PerfilProveedor::query()
            ->with('user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('perfiles_proveedores.id', 'like', '%' . $this->search . '%')
                        ->orWhere('perfiles_proveedores.nombre_publico', 'like', '%' . $this->search . '%')
                        ->orWhere('perfiles_proveedores.descripcion', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('perfiles_proveedores.estado', $this->estado);
            })
            ->when($this->estadoVerificacion !== '', function ($query) {
                $query->where('perfiles_proveedores.estado_verificacion', $this->estadoVerificacion);
            });

        if ($this->sortField === 'usuario') {
            $perfilesProveedores
                ->leftJoin('users', 'perfiles_proveedores.user_id', '=', 'users.id')
                ->select('perfiles_proveedores.*')
                ->orderBy('users.name', $this->sortDirection);
        } else {
            $perfilesProveedores->orderBy('perfiles_proveedores.' . $this->sortField, $this->sortDirection);
        }

        $perfilesProveedores = $perfilesProveedores->paginate($this->perPage);

        return [
            'perfilesProveedores' => $perfilesProveedores,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="perfiles-proveedores-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="perfiles-proveedores-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="ID, usuario, correo o nombre publico"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-3">
            <label class="form-label">Verificacion</label>
            <select class="form-select" wire:model.live="estadoVerificacion">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="rechazado">Rechazado</option>
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
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('usuario')">
                            Usuario <i class="{{ $this->sortIcon('usuario') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('nombre_publico')">
                            Nombre publico <i class="{{ $this->sortIcon('nombre_publico') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Portada</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('anios_experiencia')">
                            Experiencia <i class="{{ $this->sortIcon('anios_experiencia') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado_verificacion')">
                            Verificacion <i class="{{ $this->sortIcon('estado_verificacion') }} ms-1 small text-muted"></i>
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
                @forelse ($perfilesProveedores as $perfilProveedor)
                    <tr>
                        <td>{{ $perfilProveedor->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $perfilProveedor->user?->name ?? 'Sin usuario' }}</div>
                            <small class="text-muted">{{ $perfilProveedor->user?->email }}</small>
                        </td>
                        <td class="fw-semibold">{{ $perfilProveedor->nombre_publico }}</td>
                        <td>
                            @if ($perfilProveedor->foto_portada)
                                <img
                                    src="{{ asset($perfilProveedor->foto_portada) }}"
                                    alt="{{ $perfilProveedor->nombre_publico }}"
                                    class="rounded border"
                                    style="width: 72px; height: 48px; object-fit: cover;"
                                >
                            @else
                                <span class="text-muted">Sin portada</span>
                            @endif
                        </td>
                        <td>
                            {{ $perfilProveedor->anios_experiencia !== null ? $perfilProveedor->anios_experiencia . ' años' : 'Sin dato' }}
                        </td>
                        <td>
                            @if ($perfilProveedor->estado_verificacion === 'aprobado')
                                <span class="badge bg-success-subtle text-success">Aprobado</span>
                            @elseif ($perfilProveedor->estado_verificacion === 'rechazado')
                                <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if ($perfilProveedor->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ optional($perfilProveedor->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                @can('ver detalle proveedores')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-soft-info"
                                        title="Ver detalle"
                                        wire:click="verDetalle({{ $perfilProveedor->id }})"
                                    >
                                        <i class="ri-eye-line align-bottom"></i>
                                    </button>
                                @endcan

                                @can('editar proveedores')
                                    <a
                                        href="{{ route('perfiles-proveedores.edit', $perfilProveedor->id) }}"
                                        class="btn btn-sm btn-soft-warning"
                                        title="Editar"
                                    >
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar proveedores')
                                    <button
                                        type="button"
                                        class="btn btn-sm js-toggle-perfil-proveedor-livewire {{ $perfilProveedor->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                        title="{{ $perfilProveedor->estado ? 'Inactivar' : 'Activar' }}"
                                        data-perfil-proveedor-id="{{ $perfilProveedor->id }}"
                                        data-perfil-proveedor-nombre="{{ $perfilProveedor->nombre_publico }}"
                                        data-accion="{{ $perfilProveedor->estado ? 'inactivar' : 'activar' }}"
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
                            No se encontraron proveedores.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $perfilesProveedores->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="detalleProveedorModal" tabindex="-1" aria-labelledby="detalleProveedorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light-subtle border-0">
                    <div>
                        <h5 class="modal-title" id="detalleProveedorModalLabel">Detalle del proveedor</h5>
                        <p class="text-muted mb-0">Informacion administrativa del perfil registrado.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    @if ($this->perfilProveedorDetalle)
                        <div class="row g-4">
                            <div class="col-lg-4">
                                @if ($this->perfilProveedorDetalle->foto_portada)
                                    <img
                                        src="{{ asset($this->perfilProveedorDetalle->foto_portada) }}"
                                        alt="{{ $this->perfilProveedorDetalle->nombre_publico }}"
                                        class="rounded border w-100"
                                        style="height: 220px; object-fit: cover;"
                                    >
                                @else
                                    <div class="border rounded bg-light-subtle d-flex align-items-center justify-content-center text-muted" style="height: 220px;">
                                        Sin foto de portada
                                    </div>
                                @endif

                                <div class="mt-3 row g-2">
                                    <div class="col-sm-6 col-lg-12">
                                        <label class="form-label text-muted mb-1">Estado del perfil</label>
                                        <div>
                                            @if ($this->perfilProveedorDetalle->estado)
                                                <span class="badge bg-success-subtle text-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-12">
                                        <label class="form-label text-muted mb-1">Verificacion</label>
                                        <div>
                                            @if ($this->perfilProveedorDetalle->estado_verificacion === 'aprobado')
                                                <span class="badge bg-success-subtle text-success">Aprobado</span>
                                            @elseif ($this->perfilProveedorDetalle->estado_verificacion === 'rechazado')
                                                <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Nombre publico</label>
                                        <div class="fw-semibold">{{ $this->perfilProveedorDetalle->nombre_publico }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Usuario</label>
                                        <div class="fw-semibold">{{ $this->perfilProveedorDetalle->user?->name ?? 'Sin usuario' }}</div>
                                        <div class="text-muted">{{ $this->perfilProveedorDetalle->user?->email ?? 'Sin correo' }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Roles del usuario</label>
                                        <div>
                                            @forelse ($this->perfilProveedorDetalle->user?->roles ?? [] as $role)
                                                <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">Sin roles</span>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Años de experiencia</label>
                                        <div>{{ $this->perfilProveedorDetalle->anios_experiencia !== null ? $this->perfilProveedorDetalle->anios_experiencia : 'Sin dato' }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Estado de verificacion</label>
                                        <div class="text-capitalize">{{ $this->perfilProveedorDetalle->estado_verificacion }}</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label text-muted mb-1">Descripcion</label>
                                        <div class="border rounded p-3 bg-light-subtle" style="white-space: normal;">
                                            {{ $this->perfilProveedorDetalle->descripcion ?: 'Sin descripcion' }}
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label text-muted mb-1">Motivo de rechazo</label>
                                        <div class="border rounded p-3 bg-light-subtle" style="white-space: normal;">
                                            {{ $this->perfilProveedorDetalle->motivo_rechazo ?: 'Sin motivo registrado' }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Fecha de creacion</label>
                                        <div>{{ optional($this->perfilProveedorDetalle->created_at)->format('d/m/Y H:i') }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Ultima actualizacion</label>
                                        <div>{{ optional($this->perfilProveedorDetalle->updated_at)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            Selecciona un proveedor para ver el detalle.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
