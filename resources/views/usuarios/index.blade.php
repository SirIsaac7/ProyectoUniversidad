@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Usuarios</h4>

            @can('crear usuarios')
            <div class="page-title-right">
                <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Nuevo usuario
                </a>
            </div>
            @endcan

        </div>
    </div>
</div>

<livewire:usuarios.tabla-usuarios />
@endsection

@push('scripts')
<script src="{{ asset('assets/js/usuarios.js') }}"></script>
@endpush
