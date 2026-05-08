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
                        @endphp

                        @error('permissions')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror

                        <div class="row g-3">
                            @foreach ($permissions as $permission)
                                <div class="col-md-6 col-xl-4">
                                    <div class="card border mb-0 role-permission-card">
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
