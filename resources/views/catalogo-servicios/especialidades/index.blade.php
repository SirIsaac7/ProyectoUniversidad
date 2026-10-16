@extends('layouts.app')

@section('title', 'Especialidades')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Especialidades</h4>

            <div class="page-title-right">
                <a href="{{ route('especialidades.create') }}" class="btn btn-primary">
                    <i class="ri-add-line align-bottom me-1"></i>
                    Nueva especialidad
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de especialidades</h5>
    </div>

    <div class="card-body">
        <livewire:especialidades.tabla-especialidades />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/especialidades.js') }}"></script>
@endpush
