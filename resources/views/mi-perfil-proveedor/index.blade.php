@extends('layouts.app')

@section('title', 'Mi perfil de proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Mi perfil de proveedor</h4>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="d-none" id="mi-perfil-proveedor-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="row g-3">
    <div class="col-xl-4">
        @include('mi-perfil-proveedor.partials.datos')
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#mis-especialidades" role="tab">
                            Especialidades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#mis-horarios" role="tab">
                            Horarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#mi-ubicacion" role="tab">
                            Ubicacion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#mi-portafolio" role="tab">
                            Portafolio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#mis-documentos" role="tab">
                            Documentos
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="mis-especialidades" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.especialidades')
                    </div>

                    <div class="tab-pane" id="mis-horarios" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.horarios')
                    </div>

                    <div class="tab-pane" id="mi-ubicacion" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.ubicacion')
                    </div>

                    <div class="tab-pane" id="mi-portafolio" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.portafolio')
                    </div>

                    <div class="tab-pane" id="mis-documentos" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.documentos')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/miPerfilProveedor.js') }}"></script>
@endpush
