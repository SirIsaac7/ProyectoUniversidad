@extends('layouts.app')

@section('title', 'Tipos de servicio')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Tipos de servicio</h4>

            <div class="page-title-right">
                <a href="{{ route('tipos-servicio.create') }}" class="btn btn-primary">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Nuevo tipo de servicio
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de tipos de servicio</h5>
    </div>

    <div class="card-body">
        <livewire:tipos-servicio.tabla-tipos-servicio />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/tiposServicio.js') }}"></script>
@endpush
