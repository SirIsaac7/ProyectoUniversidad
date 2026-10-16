@extends('layouts.app')

@section('title', 'Historial de solicitudes')

@push('styles')
<link href="{{ asset('assets/css/historialSolicitudes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Historial de solicitudes</h4>

            <div class="page-title-right"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Movimientos registrados</h5>
    </div>

    <div class="card-body">
        <livewire:historial-solicitudes.tabla-historial-solicitudes />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/historialSolicitudes.js') }}"></script>
@endpush
