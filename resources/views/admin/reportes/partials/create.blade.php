@extends('layouts.app')

@section('title', 'Crear reporte')

@push('styles')
    <link href="{{ asset('assets/css/reportes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/reportes.js') }}"></script>
@endpush

@section('content')
<div class="reportes-admin">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Crear reporte</h4>
                    <p class="text-muted mb-0">Configura el tipo de informacion que se imprimira en PDF.</p>
                </div>
                <a href="{{ route('reportes.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('reportes.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.reportes.partials.form', [
            'reporte' => null,
            'submitText' => 'Guardar reporte',
        ])
    </form>
</div>
@endsection
