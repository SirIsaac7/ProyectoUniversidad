@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Editar rol</h4>

            <div class="page-title-right">
                <a href="{{ route('roles.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actualizar rol</h5>
            </div>

            <div class="card-body">
                <form class="needs-validation" novalidate method="POST" action="{{ route('roles.update', $role->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="form-label">
                            Nombre del rol <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name', $role->name) }}"
                            placeholder="Ejemplo: superadmin"
                            autocomplete="off"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Por favor ingresa el nombre del rol.</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <label class="form-label mb-0">Permisos asignados</label>
                            <span class="badge bg-info text-white fs-12">{{ $permissions->count() }} disponibles</span>
                        </div>

                        @php
                            $checkedPermissions = old('permissions', $selectedPermissions);

                            $sectoresPermisos = [
                                'Administracion' => [
                                    'icon' => 'ri-settings-3-line',
                                    'color' => 'primary',
                                    'keywords' => ['usuarios', 'roles', 'permisos', 'activitylogs', 'activity logs', 'logs'],
                                    'items' => collect(),
                                ],
                                'Mi perfil proveedor' => [
                                    'icon' => 'ri-profile-line',
                                    'color' => 'warning',
                                    'keywords' => [
                                        'visualizar perfil proveedor',
                                        'actualizar perfil proveedor',
                                        'gestionar especialidades proveedor',
                                        'gestionar horarios proveedor',
                                        'gestionar ubicacion proveedor',
                                        'gestionar portafolio proveedor',
                                        'gestionar documentos proveedor',
                                    ],
                                    'items' => collect(),
                                ],
                                'Solicitudes' => [
                                    'icon' => 'ri-calendar-check-line',
                                    'color' => 'danger',
                                    'keywords' => [
                                        'solicitudes',
                                        'mis solicitudes',
                                        'solicitudes proveedor',
                                    ],
                                    'items' => collect(),
                                ],
                                'Proveedores' => [
                                    'icon' => 'ri-user-star-line',
                                    'color' => 'info',
                                    'keywords' => [
                                        'perfiles proveedores',
                                        'perfil proveedor',
                                        'proveedor especialidades',
                                        'especialidades proveedor',
                                        'horarios proveedor',
                                        'ubicaciones proveedor',
                                        'ubicacion proveedor',
                                        'portafolio proveedor',
                                        'documentos proveedor',
                                        'tipos documento proveedor',
                                        'tipo documento proveedor',
                                    ],
                                    'items' => collect(),
                                ],
                                'Catalogo de servicios' => [
                                    'icon' => 'ri-folder-settings-line',
                                    'color' => 'success',
                                    'keywords' => ['rubros', 'tipos de servicio', 'tipo servicio', 'especialidades'],
                                    'items' => collect(),
                                ],
                                'Otros permisos' => [
                                    'icon' => 'ri-more-2-line',
                                    'color' => 'secondary',
                                    'keywords' => [],
                                    'items' => collect(),
                                ],
                            ];

                            foreach ($permissions as $permission) {
                                $nombrePermiso = mb_strtolower($permission->name);
                                $sectorAsignado = null;

                                foreach ($sectoresPermisos as $sectorNombre => $sector) {
                                    if ($sectorNombre === 'Otros permisos') {
                                        continue;
                                    }

                                    foreach ($sector['keywords'] as $keyword) {
                                        if (str_contains($nombrePermiso, mb_strtolower($keyword))) {
                                            $sectorAsignado = $sectorNombre;
                                            break 2;
                                        }
                                    }
                                }

                                $sectoresPermisos[$sectorAsignado ?? 'Otros permisos']['items']->push($permission);
                            }

                            $sectoresPermisos = collect($sectoresPermisos)
                                ->filter(fn ($sector) => $sector['items']->isNotEmpty());
                        @endphp

                        @error('permissions')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror

                        <div class="accordion custom-accordionwithicon" id="accordionPermisosRol">
                            @foreach ($sectoresPermisos as $sectorNombre => $sector)
                                @php
                                    $sectorId = 'sector-permisos-' . Str::slug($sectorNombre);
                                    $permisosMarcadosSector = $sector['items']
                                        ->filter(fn ($permission) => in_array($permission->id, $checkedPermissions))
                                        ->count();
                                @endphp

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $sectorId }}">
                                        <button
                                            class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $sectorId }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                            aria-controls="collapse-{{ $sectorId }}"
                                        >
                                            <span class="avatar-xs me-2">
                                                <span class="avatar-title rounded bg-{{ $sector['color'] }}-subtle text-{{ $sector['color'] }}">
                                                    <i class="{{ $sector['icon'] }}"></i>
                                                </span>
                                            </span>
                                            <span class="fw-semibold">{{ $sectorNombre }}</span>
                                            <span class="badge bg-light text-muted ms-2">
                                                {{ $permisosMarcadosSector }}/{{ $sector['items']->count() }}
                                            </span>
                                        </button>
                                    </h2>

                                    <div
                                        id="collapse-{{ $sectorId }}"
                                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                        aria-labelledby="heading-{{ $sectorId }}"
                                        data-bs-parent="#accordionPermisosRol"
                                    >
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                @foreach ($sector['items'] as $permission)
                                                    <div class="col-md-6 col-xl-4">
                                                        <div class="card border mb-0 role-permission-card h-100">
                                                            <div class="card-body py-3">
                                                                <div class="form-check form-switch form-switch-md d-flex align-items-center justify-content-between mb-0">
                                                                    <label class="form-check-label fw-medium pe-3" for="permission_{{ $permission->id }}">
                                                                        {{ $permission->name }}
                                                                    </label>
                                                                    <input
                                                                        class="form-check-input flex-shrink-0"
                                                                        type="checkbox"
                                                                        value="{{ $permission->id }}"
                                                                        id="permission_{{ $permission->id }}"
                                                                        name="permissions[]"
                                                                        @checked(in_array($permission->id, $checkedPermissions))
                                                                    >
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('roles.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Actualizar rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/roles.js') }}"></script>
@endpush
