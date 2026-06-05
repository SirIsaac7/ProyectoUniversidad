@extends('layouts.app')

@section('title', 'Solicitudes')

@push('styles')
<link href="{{ asset('assets/css/solicitudes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Solicitudes</h4>

            <div class="page-title-right"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de solicitudes</h5>
    </div>

    <div class="card-body">
        <livewire:solicitudes.tabla-solicitudes />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/solicitudes.js') }}"></script>
@endpush
