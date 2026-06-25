<?php

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $tipoAcceso = '';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarCambioEstadoUsuario' => 'toggleEstado',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedTipoAcceso(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function toggleEstado(int $usuarioId): void
    {
        abort_unless(auth()->user()->can('eliminar usuarios'), 403);

        $usuario = User::findOrFail($usuarioId);

        if ((int) $usuario->id === (int) auth()->id()) {
            $this->dispatch('usuario-estado-error', message: 'No puedes cambiar el estado de tu propia cuenta.');
            return;
        }

        $usuario->update([
            'estado' => ! $usuario->estado,
        ]);

        $this->dispatch(
            'usuario-estado-cambiado',
            message: $usuario->estado
                ? 'Usuario activado correctamente.'
                : 'Usuario inactivado correctamente.'
        );
    }

    public function with(): array
    {
        $usuarios = User::with('roles')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('estado', $this->estado);
            })
            ->when($this->tipoAcceso !== '', function ($query) {
                if ($this->tipoAcceso === 'google') {
                    $query->whereNotNull('google_id');
                }

                if ($this->tipoAcceso === 'manual') {
                    $query->whereNull('google_id');
                }
            })
            ->orderBy('id')
            ->paginate($this->perPage);

        return [
            'usuarios' => $usuarios,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="usuarios-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="usuarios-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input
                type="text"
                class="form-control"
                placeholder="Nombre, correo o ID"
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" wire:model.live="estado">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Tipo de acceso</label>
            <select class="form-select" wire:model.live="tipoAcceso">
                <option value="">Todos</option>
                <option value="manual">Manual</option>
                <option value="google">Google</option>
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
                    <th>ID</th>
                    <th>Avatar</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Celular</th>
                    <th>WhatsApp</th>
                    <th>Acceso</th>
                    <th>Verificado</th>
                    <th>Estado</th>
                    <th>Roles</th>
                    <th>Creado</th>
                    <th>Actualizado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->id }}</td>

                        <td>
                            @if ($usuario->avatar_url)
                                <img
                                    src="{{ $usuario->avatar_url }}"
                                    alt="Avatar"
                                    class="rounded-circle"
                                    width="40"
                                    height="40"
                                    referrerpolicy="no-referrer"
                                    style="object-fit: cover;"
                                >
                            @else
                                <div class="avatar-xs">
                                    <div class="avatar-title bg-light text-primary rounded-circle">
                                        {{ $usuario->inicial }}
                                    </div>
                                </div>
                            @endif
                        </td>

                        <td><span class="fw-semibold">{{ $usuario->name }}</span></td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->celular ?: 'Sin celular' }}</td>
                        <td>
                            @if ($usuario->recibe_notificaciones_whatsapp && $usuario->celular && $usuario->celular_verificado_at)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @elseif ($usuario->recibe_notificaciones_whatsapp)
                                <span class="badge bg-warning-subtle text-warning">
                                    {{ $usuario->celular ? 'Sin verificar' : 'Sin celular' }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">No</span>
                            @endif
                        </td>

                        <td>
                            @if ($usuario->google_id)
                                <span class="badge bg-danger-subtle text-danger">Google</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Manual</span>
                            @endif
                        </td>

                        <td>
                            @if ($usuario->email_verified_at)
                                <span class="badge bg-success-subtle text-success">Si</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">No</span>
                            @endif
                        </td>

                        <td>
                            @if ($usuario->estado)
                                <span class="badge bg-success-subtle text-success">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse ($usuario->roles as $role)
                                    <span class="badge bg-info-subtle text-info">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">Sin roles</span>
                                @endforelse
                            </div>
                        </td>

                        <td>{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($usuario->updated_at)->format('d/m/Y H:i') }}</td>

                        <td>
                            <div class="hstack gap-2">
                                @can('editar usuarios')
                                <a
                                    href="{{ route('usuarios.edit', $usuario->id) }}"
                                    class="btn btn-sm btn-soft-warning"
                                    title="Editar"
                                >
                                    <i class="ri-pencil-fill align-bottom"></i>
                                </a>
                                @endcan

                                @can('asignar rol usuarios')
                                <a
                                    href="{{ route('usuarios.roles.edit', $usuario->id) }}"
                                    class="btn btn-sm btn-soft-primary"
                                    title="Asignar rol"
                                >
                                    <i class="ri-shield-user-line align-bottom"></i>
                                </a>
                                @endcan

                                @can('eliminar usuarios')
                                <button
                                    type="button"
                                    class="btn btn-sm js-toggle-usuario-livewire {{ $usuario->estado ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                    title="{{ $usuario->estado ? 'Inactivar' : 'Activar' }}"
                                    data-usuario-id="{{ $usuario->id }}"
                                    data-usuario-nombre="{{ $usuario->name }}"
                                    data-accion="{{ $usuario->estado ? 'inactivar' : 'activar' }}"
                                >
                                    <i class="ri-refresh-line align-bottom"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted py-4">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $usuarios->links() }}
    </div>
</div>
