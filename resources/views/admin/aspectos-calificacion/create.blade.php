@extends('layouts.app')

@section('title', 'Crear aspecto de calificación')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Crear aspecto de calificación</h4>

            <div class="page-title-right">
                <a href="{{ route('aspectos-calificacion.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Formulario de aspecto</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation" novalidate method="POST" action="{{ route('aspectos-calificacion.store') }}">
            @csrf

            @include('admin.aspectos-calificacion.partials.form')

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('aspectos-calificacion.index') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line align-bottom me-1"></i>
                    Guardar aspecto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/aspectosCalificacion.js') }}"></script>
@endpush
