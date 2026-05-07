@extends('layouts.app')

@section('title', 'Editar especialidad')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col">
                <h4 class="mb-sm-0">Editar especialidad</h4>
            </div>

            <div class="col-auto">
                <a href="{{ route('especialidades.index') }}" class="btn btn-light">
                    Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actualizar especialidad</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('especialidades.update', $especialidad->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rubro_tipo_servicio_id" class="form-label">Rubro y tipo de servicio</label>
                            <select
                                name="rubro_tipo_servicio_id"
                                id="rubro_tipo_servicio_id"
                                class="form-select js-rubro-tipo-servicio-select @error('rubro_tipo_servicio_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Selecciona una opcion</option>
                                @foreach ($rubrosTiposServicio as $rubroTipoServicio)
                                    <option
                                        value="{{ $rubroTipoServicio->id }}"
                                        @selected(old('rubro_tipo_servicio_id', $especialidad->rubro_tipo_servicio_id) == $rubroTipoServicio->id)
                                    >
                                        {{ $rubroTipoServicio->rubro->nombre }} - {{ $rubroTipoServicio->tipoServicio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rubro_tipo_servicio_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre', $especialidad->nombre) }}"
                                required
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="imagen" class="form-label">Imagen</label>
                            <input
                                type="file"
                                name="imagen"
                                id="imagen"
                                class="form-control @error('imagen') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp"
                            >
                            @error('imagen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado actual</label>
                            <div>
                                @if ($especialidad->estado)
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                                @endif
                            </div>
                        </div>

                        @if ($especialidad->imagen)
                            <div class="col-md-6">
                                <label class="form-label">Imagen actual</label>
                                <div>
                                    <img
                                        src="{{ asset($especialidad->imagen) }}"
                                        alt="{{ $especialidad->nombre }}"
                                        class="rounded border"
                                        style="width: 90px; height: 90px; object-fit: cover;"
                                    >
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea
                                name="descripcion"
                                id="descripcion"
                                rows="4"
                                class="form-control @error('descripcion') is-invalid @enderror"
                            >{{ old('descripcion', $especialidad->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('especialidades.index') }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    Actualizar especialidad
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/especialidades.js') }}"></script>
@endpush
