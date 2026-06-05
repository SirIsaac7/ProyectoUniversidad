@extends('layouts.app')

@section('title', 'Rubros')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Rubros</h4>

            <div class="page-title-right">
                <a href="{{ route('rubros.create') }}" class="btn btn-primary">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Nuevo rubro
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de rubros</h5>
    </div>

    <div class="card-body">
        <livewire:rubros.tabla-rubros />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/rubros.js') }}"></script>
@endpush
