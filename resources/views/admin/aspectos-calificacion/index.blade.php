@extends('layouts.app')

@section('title', 'Aspectos de calificación')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <div>
                <h4 class="mb-1">Aspectos de calificación</h4>
                <p class="text-muted mb-0">Define los criterios que el cliente evaluará al calificar una cita.</p>
            </div>

            <div class="page-title-right">
                @can('crear aspectos calificacion')
                    <a href="{{ route('aspectos-calificacion.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>
                        Nuevo aspecto
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Listado de aspectos</h5>
    </div>

    <div class="card-body">
        <livewire:aspectos-calificacion.tabla-aspectos-calificacion />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/aspectosCalificacion.js') }}"></script>
@endpush
