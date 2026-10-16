@extends('layouts.app')

@section('title', 'Calificaciones')

@push('styles')
<link href="{{ asset('assets/css/calificaciones.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <div>
                <h4 class="mb-1">Calificaciones</h4>
                <p class="text-muted mb-0">Revisa, oculta o elimina reseñas registradas en el sistema.</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de calificaciones</h5>
    </div>

    <div class="card-body">
        <livewire:calificaciones.tabla-calificaciones />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/calificaciones.js') }}"></script>
@endpush
