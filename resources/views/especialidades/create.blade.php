@extends('layouts.app')

@section('title', 'Crear especialidad')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col">
                <h4 class="mb-sm-0">Crear especialidad</h4>
            </div>

            <div class="col-auto">
                <a href="{{ route('especialidades.index') }}" class="btn btn-light">
                    Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Formulario de especialidad</h5>
            </div>

            <div class="card-body">
                <form class="needs-validation" novalidate method="POST" action="{{ route('especialidades.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rubro_tipo_servicio_id" class="form-label">
                                Rubro y tipo de servicio <span class="text-danger">*</span>
                            </label>
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
                                        @selected(old('rubro_tipo_servicio_id') == $rubroTipoServicio->id)
                                    >
                                        {{ $rubroTipoServicio->rubro->nombre }} - {{ $rubroTipoServicio->tipoServicio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rubro_tipo_servicio_id')
                                <div class="invalid-feedback d-block js-rubro-tipo-servicio-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback js-rubro-tipo-servicio-feedback">Por favor selecciona un rubro y tipo de servicio.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre') }}"
                                required
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa el nombre de la especialidad.</div>
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
                            <label for="estado" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select
                                name="estado"
                                id="estado"
                                class="form-select @error('estado') is-invalid @enderror"
                                required
                            >
                                <option value="1" @selected(old('estado', '1') === '1')>Activo</option>
                                <option value="0" @selected(old('estado') === '0')>Inactivo</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor selecciona el estado.</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea
                                name="descripcion"
                                id="descripcion"
                                rows="4"
                                class="form-control @error('descripcion') is-invalid @enderror"
                            >{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('especialidades.index') }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    Guardar especialidad
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
