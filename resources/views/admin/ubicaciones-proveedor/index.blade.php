@extends('layouts.app')

@section('title', 'Ubicaciones del proveedor')

@push('styles')
<link href="{{ asset('assets/css/ubicacionesProveedor.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Ubicaciones del proveedor</h4>

            <div class="page-title-right">
                @can('crear ubicaciones proveedor')
                    <a href="{{ route('ubicaciones-proveedor.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nueva ubicacion
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Mapa de cobertura</h5>
    </div>

    <div class="card-body">
        <livewire:ubicaciones-proveedor.tabla-ubicaciones-proveedor />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/ubicacionesProveedor.js') }}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initUbicacionesProveedorMaps"></script>
@endpush
