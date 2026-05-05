@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Roles</h4>

            <div class="page-title-right">
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Nuevo rol
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Listado de roles</h5>
            </div>
            <div class="card-body">
                @if ($roles->isEmpty())
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-4">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                <i class="ri-admin-line"></i>
                            </div>
                        </div>
                        <h5 class="mb-2">No hay roles registrados</h5>
                        <p class="text-muted mb-4">Crea el primer rol y asignale permisos del sistema.</p>
                        @can('crear roles')
                        <a href="{{ route('roles.create') }}" class="btn btn-primary">Crear rol</a>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table
                            id="tabla-roles"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%"
                            data-success-message="{{ session('success') }}"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Permisos</th>
                                    <th>Fecha de creacion</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td><span class="fw-semibold">{{ $role->name }}</span></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse ($role->permissions as $permission)
                                                    <span class="badge bg-info-subtle text-info">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="text-muted">Sin permisos</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>{{ optional($role->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="hstack gap-2">
                                                @can('editar roles')
                                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-soft-warning" title="Editar">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </a>
                                                @endcan

                                                <form
                                                    action="{{ route('roles.destroy', $role->id) }}"
                                                    method="POST"
                                                    class="form-delete-rol"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    @can('eliminar roles')
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-soft-danger"
                                                        title="Eliminar"
                                                        data-rol-nombre="{{ $role->name }}"
                                                    >
                                                        <i class="ri-delete-bin-fill align-bottom"></i>
                                                    </button>
                                                    @endcan
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/roles.js') }}"></script>
@endpush
