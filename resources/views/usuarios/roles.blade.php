@extends('layouts.app')

@section('title', 'Asignar rol')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Asignar rol</h4>

            <div class="page-title-right">
                <a href="{{ route('usuarios.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Rol del usuario</h5>
            </div>

            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Usuario</label>
                    <div class="form-control bg-light">{{ $usuario->name }} - {{ $usuario->email }}</div>
                </div>

                <form class="needs-validation" novalidate method="POST" action="{{ route('usuarios.roles.update', $usuario->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                            <option value="">Sin rol</option>
                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->name }}"
                                    @selected(old('role', $usuario->roles->first()?->name) === $role->name)
                                >
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Solo se permite un rol por usuario.</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Guardar rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
