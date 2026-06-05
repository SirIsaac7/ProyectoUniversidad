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
@endpush
