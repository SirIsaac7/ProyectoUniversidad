@extends('layouts.app')

@section('title', 'Tipos de documento del proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Tipos de documento del proveedor</h4>

            <div class="page-title-right">
                @can('crear tipos documento proveedor')
                    <a href="{{ route('tipos-documento-proveedor.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nuevo tipo
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de tipos de documento</h5>
    </div>

    <div class="card-body">
        <livewire:tipos-documento-proveedor.tabla-tipos-documento-proveedor />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/tiposDocumentoProveedor.js') }}"></script>
@endpush
