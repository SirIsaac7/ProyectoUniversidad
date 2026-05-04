@extends('layouts.app')

@section('title', 'Permisos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Permisos</h4>

            @can('crear permisos')
            <div class="page-title-right">
                <a href="{{ route('permisos.create') }}" class="btn btn-primary">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Nuevo permiso
                </a>
            </div>
            @endcan

        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Listado de permisos</h5>
            </div>
            <div class="card-body">
                @if ($permissions->isEmpty())
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-4">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                <i class="ri-shield-keyhole-line"></i>
                            </div>
                        </div>
                        <h5 class="mb-2">No hay permisos registrados</h5>
                        <p class="text-muted mb-4">Crea el primer permiso para empezar a construir roles.</p>
                        <a href="{{ route('permisos.create') }}" class="btn btn-primary">
                            Crear permiso
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table
                            id="tabla-permisos"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%"
                            data-success-message="{{ session('success') }}"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Guard</th>
                                    <th>Fecha de creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->id }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $permission->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">{{ $permission->guard_name }}</span>
                                        </td>
                                        <td>{{ optional($permission->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="hstack gap-2">
                                                @can('editar permisos')
                                                <a
                                                    href="{{ route('permisos.edit', ['permiso' => $permission->id]) }}"
                                                    class="btn btn-sm btn-soft-warning"
                                                    title="Editar"
                                                >
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </a>
                                                @endcan

                                                <form action="{{ route('permisos.destroy', ['permiso' => $permission->id]) }}" method="POST" onsubmit="return confirm('Eliminar este permiso?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    @can('eliminar permisos')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Eliminar">
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
<script src="{{ asset('assets/js/permisos.js') }}"></script>
@endpush
