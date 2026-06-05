@extends('layouts.app')

@section('title', 'Citas')

@push('styles')
<link href="{{ asset('assets/css/citas.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Citas</h4>

            <div class="page-title-right"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de citas</h5>
    </div>

    <div class="card-body">
        <livewire:citas.tabla-citas />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/citas.js') }}"></script>
@endpush
