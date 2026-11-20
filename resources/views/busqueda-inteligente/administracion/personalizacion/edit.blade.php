@extends('layouts.app')

@section('title', 'Personalización de búsqueda')

@push('styles')
<link href="{{ asset('assets/css/motorBusquedaAdmin.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div id="motorBusquedaPanel">
    <div class="page-title-box bg-transparent">
        <h4 class="mb-1">Personalización de búsqueda</h4>
        <p class="text-muted mb-0">Configura la apariencia del buscador semántico de los clientes.</p>
    </div>

    @include('busqueda-inteligente.administracion.partials.personalizacion')
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/motorBusquedaAdmin.js') }}"></script>
@endpush
