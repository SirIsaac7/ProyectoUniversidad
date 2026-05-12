@extends('layouts.app')

@section('title', 'Portafolio del proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Portafolio del proveedor</h4>

            <div class="page-title-right">
                @can('crear portafolio proveedor')
                    <a href="{{ route('portafolio-proveedor.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nuevo trabajo
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Trabajos realizados</h5>
    </div>

    <div class="card-body">
        <livewire:portafolio-proveedor.tabla-portafolio-proveedor />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/portafolioProveedor.js') }}"></script>
@endpush
