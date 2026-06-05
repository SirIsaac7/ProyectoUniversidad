@extends('layouts.app')

@section('title', 'Horarios del proveedor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Horarios del proveedor</h4>

            <div class="page-title-right">
                @can('crear horarios proveedor')
                    <a href="{{ route('horarios-proveedor.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nuevo horario
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Agenda semanal de proveedores</h5>
    </div>

    <div class="card-body">
        <livewire:horarios-proveedor.tabla-horarios-proveedor />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/horariosProveedor.js') }}"></script>
@endpush
