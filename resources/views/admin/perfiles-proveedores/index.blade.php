@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Proveedores</h4>

            <div class="page-title-right">
                @can('crear proveedores')
                    <a href="{{ route('perfiles-proveedores.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nuevo proveedor
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de proveedores</h5>
    </div>

    <div class="card-body">
        <livewire:perfiles-proveedores.tabla-perfiles-proveedores />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/perfilesProveedores.js') }}"></script>
@endpush
