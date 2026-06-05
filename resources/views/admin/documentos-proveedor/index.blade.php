@extends('layouts.app')

@section('title', 'Documentos del proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Documentos del proveedor</h4>

            <div class="page-title-right">
                @can('crear documentos proveedor')
                    <a href="{{ route('documentos-proveedor.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nuevo documento
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de documentos</h5>
    </div>

    <div class="card-body">
        <livewire:documentos-proveedor.tabla-documentos-proveedor />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/documentosProveedor.js') }}"></script>
@endpush
