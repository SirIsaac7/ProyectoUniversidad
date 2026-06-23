@extends('layouts.app')

@section('title', 'Editar reporte')

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
                    <h4 class="mb-1">Editar reporte</h4>
                    <p class="text-muted mb-0">Actualiza filtros, rango, contenido y apariencia del PDF.</p>
                </div>
                <a href="{{ route('reportes.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('reportes.update', $reporte) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.reportes.partials.form', [
            'reporte' => $reporte,
            'submitText' => 'Actualizar reporte',
        ])
    </form>
</div>
@endsection
