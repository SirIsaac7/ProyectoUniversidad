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
    <div class="col-xl-4" id="miPerfilProveedorDatos">
        @include('mi-perfil-proveedor.partials.datos')
    </div>

    <div class="col-xl-8" id="miPerfilProveedorSecciones">
        <div class="card">
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="mis-especialidades" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.especialidades')
                    </div>

                    <div class="tab-pane fade" id="mis-horarios" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.horarios')
                    </div>

                    <div class="tab-pane fade" id="mi-ubicacion" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.ubicacion')
                    </div>

                    <div class="tab-pane fade" id="mi-portafolio" role="tabpanel">
                        @include('mi-perfil-proveedor.partials.portafolio')
                    </div>

                    <div class="tab-pane fade" id="mis-documentos" role="tabpanel">
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
