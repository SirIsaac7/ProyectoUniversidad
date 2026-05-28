@extends('layouts.app')

@section('title', 'Mi ubicacion')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mi ubicacion</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="card">
    <div class="card-body">
        @include('mi-perfil-proveedor.partials.ubicacion')
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/Mi-Perfil-Proveedor/miPerfilProveedor.js') }}"></script>
@endpush
