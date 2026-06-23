@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@if ($tipoInicio === 'proveedor')
    @include('inicio.partials.proveedor', ['data' => $inicioProveedor])
@elseif ($tipoInicio === 'admin')
    @include('inicio.partials.admin', ['data' => $inicioAdmin])
@elseif ($tipoInicio === 'cliente')
    @include('inicio.partials.cliente', ['data' => $inicioCliente])
@else
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <h4 class="mb-sm-0">Inicio</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Bienvenido</h5>
            <p class="card-text">Tu panel de inicio se adaptara segun los modulos disponibles para tu rol.</p>
        </div>
    </div>
@endif
@endsection

@push('styles')
<link href="{{ asset('assets/css/inicio.css') }}" rel="stylesheet" type="text/css" />
@if ($tipoInicio === 'admin')
    <link href="{{ asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/mapaLaPaz.css') }}" rel="stylesheet" type="text/css" />
@endif
@if (in_array($tipoInicio, ['admin', 'proveedor'], true))
    <link href="{{ asset('assets/css/calificaciones.css') }}" rel="stylesheet" type="text/css" />
@endif
@endpush

@if (in_array($tipoInicio, ['admin', 'proveedor', 'cliente'], true))
    @push('scripts')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    @if ($tipoInicio === 'admin')
    <script src="{{ asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/js/maps/LaPaz/datos-lapaz.js') }}"></script>
    <script src="{{ asset('assets/js/maps/LaPaz/app.js') }}"></script>
    @endif
    <script src="{{ asset('assets/js/inicio.js') }}"></script>
    @if ($tipoInicio === 'proveedor')
    <script src="{{ asset('assets/js/calificaciones.js') }}"></script>
    @endif
    @endpush
@endif
