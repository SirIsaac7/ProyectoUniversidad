@extends('layouts.app')

@section('title', 'Editar trabajo de portafolio')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Editar trabajo de portafolio</h4>

            <div class="page-title-right">
                <a href="{{ route('portafolio-proveedor.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Actualizar trabajo realizado</h5>
    </div>

    <div class="card-body">
        <form class="needs-validation form-steps js-portafolio-wizard" novalidate method="POST" action="{{ route('portafolio-proveedor.update', $portafolioProveedor->id) }}" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="text-center pt-3 pb-4 mb-1">
                <h5>Portafolio del proveedor</h5>
            </div>

            <div id="portafolio-progress-bar" class="progress-nav portafolio-progress-nav mb-4">
                <div class="progress" style="height: 1px;">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <ul class="nav nav-pills progress-bar-tab custom-nav" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill active" data-progressbar="portafolio-progress-bar" id="portafolio-datos-tab" data-bs-toggle="pill" data-bs-target="#portafolio-datos" type="button" role="tab" aria-controls="portafolio-datos" aria-selected="true">
                            <span class="step-number">1</span>
                            <span class="step-title">Datos</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill" data-progressbar="portafolio-progress-bar" id="portafolio-imagenes-tab" data-bs-toggle="pill" data-bs-target="#portafolio-imagenes" type="button" role="tab" aria-controls="portafolio-imagenes" aria-selected="false" disabled>
                            <span class="step-number">2</span>
                            <span class="step-title">Imagenes</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill" data-progressbar="portafolio-progress-bar" id="portafolio-revision-tab" data-bs-toggle="pill" data-bs-target="#portafolio-revision" type="button" role="tab" aria-controls="portafolio-revision" aria-selected="false" disabled>
                            <span class="step-number">3</span>
                            <span class="step-title">Revision</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="portafolio-datos" role="tabpanel" aria-labelledby="portafolio-datos-tab">
                    <div class="mb-4">
                        <h5 class="mb-1">Datos del trabajo</h5>
                        <p class="text-muted mb-0">Actualiza el proveedor y la informacion principal del trabajo realizado.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="perfil_proveedor_id" class="form-label">
                                Proveedor <span class="text-danger">*</span>
                            </label>
                            <select
                                name="perfil_proveedor_id"
                                id="perfil_proveedor_id"
                                class="form-select js-perfil-proveedor-select @error('perfil_proveedor_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Selecciona un proveedor</option>
                                @foreach ($perfilesProveedores as $perfilProveedor)
                                    <option value="{{ $perfilProveedor->id }}" @selected(old('perfil_proveedor_id', $portafolioProveedor->perfil_proveedor_id) == $perfilProveedor->id)>
                                        {{ $perfilProveedor->nombre_publico }} - {{ $perfilProveedor->user?->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('perfil_proveedor_id')
                                <div class="invalid-feedback d-block js-perfil-proveedor-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback js-perfil-proveedor-feedback">Por favor selecciona un proveedor.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="titulo" class="form-label">
                                Titulo <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $portafolioProveedor->titulo) }}" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingresa el titulo del trabajo.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_trabajo" class="form-label">Fecha del trabajo</label>
                            <input type="date" name="fecha_trabajo" id="fecha_trabajo" class="form-control @error('fecha_trabajo') is-invalid @enderror" value="{{ old('fecha_trabajo', optional($portafolioProveedor->fecha_trabajo)->toDateString()) }}" max="{{ now()->toDateString() }}">
                            @error('fecha_trabajo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="estado" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="1" @selected(old('estado', $portafolioProveedor->estado ? '1' : '0') === '1')>Activo</option>
                                <option value="0" @selected(old('estado', $portafolioProveedor->estado ? '1' : '0') === '0')>Inactivo</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripcion general</label>
                            <textarea name="descripcion" id="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $portafolioProveedor->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mt-4">
                        <button type="button" class="btn btn-success btn-label right ms-auto nexttab" data-nexttab="portafolio-imagenes-tab">
                            <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                            Continuar a imagenes
                        </button>
                    </div>
                </div>

                <div class="tab-pane fade" id="portafolio-imagenes" role="tabpanel" aria-labelledby="portafolio-imagenes-tab">
                    @if ($portafolioProveedor->imagenes->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="mb-1">Imagenes actuales</h5>
                            <p class="text-muted mb-0">Actualiza el titulo, descripcion o visibilidad de las imagenes guardadas.</p>
                        </div>

                        <div class="row g-3 mb-4">
                            @foreach ($portafolioProveedor->imagenes as $imagen)
                                <div class="col-lg-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex gap-3">
                                            <img src="{{ asset($imagen->imagen) }}" alt="{{ $imagen->titulo ?? $portafolioProveedor->titulo }}" class="rounded border" style="width: 96px; height: 96px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <input type="text" name="imagenes_existentes[{{ $imagen->id }}][titulo]" class="form-control" value="{{ old('imagenes_existentes.' . $imagen->id . '.titulo', $imagen->titulo) }}" placeholder="Titulo de la imagen">
                                                <textarea name="imagenes_existentes[{{ $imagen->id }}][descripcion]" rows="2" class="form-control mt-2" placeholder="Descripcion">{{ old('imagenes_existentes.' . $imagen->id . '.descripcion', $imagen->descripcion) }}</textarea>
                                                <div class="form-check form-switch mt-2">
                                                    <input type="hidden" name="imagenes_existentes[{{ $imagen->id }}][estado]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="imagenes_existentes[{{ $imagen->id }}][estado]" value="1" id="imagen_estado_{{ $imagen->id }}" @checked(old('imagenes_existentes.' . $imagen->id . '.estado', $imagen->estado ? '1' : '0') == '1')>
                                                    <label class="form-check-label" for="imagen_estado_{{ $imagen->id }}">Visible</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Agregar nuevas imagenes</h5>
                            <p class="text-muted mb-0">Puedes sumar nuevas evidencias del mismo trabajo.</p>
                        </div>
                        <span class="badge bg-info text-white">Arrastra para ordenar</span>
                    </div>

                    @php
                        $titulosImagenes = ['Antes', 'Durante', 'Despues', 'Detalle'];
                        $ayudasImagenes = [
                            'Antes' => 'Estado inicial del problema o del lugar antes de intervenir.',
                            'Durante' => 'Proceso del trabajo, avance o procedimiento realizado.',
                            'Despues' => 'Resultado final luego de terminar el servicio.',
                            'Detalle' => 'Acercamiento, pieza, acabado o evidencia importante.',
                        ];
                        $ayudaPersonalizada = 'Titulo personalizado para una evidencia especifica del trabajo.';
                    @endphp

                    <div class="row g-3 js-portafolio-imagenes-sortable">
                        @foreach ($titulosImagenes as $i => $tituloImagen)
                            @php
                                $valorTitulo = old('imagenes_titulo.' . $i, $tituloImagen);
                                $personalizado = old('imagenes_titulo.' . $i) && old('imagenes_titulo.' . $i) !== $tituloImagen;
                            @endphp
                            <div class="col-lg-6 js-portafolio-imagen-card">
                                <div class="border rounded p-3 h-100 portafolio-imagen-card">
                                    <div class="text-center mb-3">
                                        <img
                                            src="{{ asset('assets/images/users/user-dummy-img.jpg') }}"
                                            alt="Previsualizacion de imagen"
                                            class="rounded border js-portafolio-imagen-preview"
                                            style="width: 100%; max-width: 220px; height: 140px; object-fit: cover;"
                                        >
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar-xs flex-shrink-0">
                                                <span class="avatar-title rounded-circle bg-info-subtle text-info">
                                                    <i class="ri-drag-move-2-line js-portafolio-imagen-handle"></i>
                                                </span>
                                            </span>
                                            <div>
                                                <label class="form-label mb-0" for="imagen_titulo_{{ $i }}">
                                                    Titulo de la imagen
                                                </label>
                                                <small class="text-muted d-block">Puedes mover esta tarjeta de posicion.</small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mb-0">
                                            <input
                                                class="form-check-input js-imagen-titulo-custom-switch"
                                                type="checkbox"
                                                role="switch"
                                                id="custom_titulo_{{ $i }}"
                                                @checked($personalizado)
                                            >
                                            <label class="form-check-label" for="custom_titulo_{{ $i }}">Personalizar</label>
                                        </div>
                                    </div>

                                    <input
                                        type="text"
                                        id="imagen_titulo_{{ $i }}"
                                        name="imagenes_titulo[]"
                                        class="form-control js-imagen-titulo-input"
                                        value="{{ $valorTitulo }}"
                                        data-default-title="{{ $tituloImagen }}"
                                        readonly
                                    >
                                    <span
                                        class="badge rounded-pill bg-info-subtle text-info mt-2 js-imagen-titulo-badge"
                                        data-default-info="{{ $ayudasImagenes[$tituloImagen] }}"
                                        data-custom-info="{{ $ayudaPersonalizada }}"
                                    >
                                        {{ $personalizado ? $ayudaPersonalizada : $ayudasImagenes[$tituloImagen] }}
                                    </span>

                                    <input type="file" name="imagenes[]" class="form-control mt-3 js-portafolio-imagen-input @error('imagenes.' . $i) is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                                    @error('imagenes.' . $i)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <textarea name="imagenes_descripcion[]" rows="2" class="form-control mt-2" placeholder="Descripcion opcional">{{ old('imagenes_descripcion.' . $i) }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex align-items-start gap-3 mt-4">
                        <button type="button" class="btn btn-light btn-label previestab" data-previous="portafolio-datos-tab">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                            Volver a datos
                        </button>
                        <button type="button" class="btn btn-success btn-label right ms-auto nexttab" data-nexttab="portafolio-revision-tab">
                            <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                            Revisar
                        </button>
                    </div>
                </div>

                <div class="tab-pane fade" id="portafolio-revision" role="tabpanel" aria-labelledby="portafolio-revision-tab">
                    <div class="text-center py-4">
                        <div class="avatar-md mx-auto mb-4">
                            <div class="avatar-title bg-light text-success display-4 rounded-circle">
                                <i class="ri-checkbox-circle-fill"></i>
                            </div>
                        </div>
                        <h5>Listo para actualizar</h5>
                        <p class="text-muted mb-0">Verifica los cambios y actualiza el trabajo del portafolio.</p>
                    </div>

                    <div class="d-flex align-items-start gap-3 mt-4">
                        <button type="button" class="btn btn-light btn-label previestab" data-previous="portafolio-imagenes-tab">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                            Volver a imagenes
                        </button>
                        <button type="submit" class="btn btn-primary btn-label right ms-auto">
                            <i class="ri-save-line label-icon align-middle fs-16 ms-2"></i>
                            Actualizar trabajo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/libs/sortablejs/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/js/portafolioProveedor.js') }}"></script>
@endpush
