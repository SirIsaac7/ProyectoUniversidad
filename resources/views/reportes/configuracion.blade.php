@extends('layouts.app')

@section('title', 'Configuracion de reportes')

@push('styles')
    <link href="{{ asset('assets/css/reportes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="reportes-admin">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Configuracion general de reportes</h4>
                    <p class="text-muted mb-0">Estos ajustes se aplicaran a todos los PDF generados por el sistema.</p>
                </div>
                <a href="{{ route('reportes.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('reportes.configuracion.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Apariencia del PDF</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="mostrar_logo" value="0">
                        <input type="hidden" name="mostrar_fecha" value="0">
                        <input type="hidden" name="mostrar_generado_por" value="0">
                        <input type="hidden" name="quitar_logo" value="0">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hoja <span class="text-danger">*</span></label>
                                <select name="tamano_hoja" class="form-select @error('tamano_hoja') is-invalid @enderror">
                                    @foreach ($tamanoHoja as $valor => $texto)
                                        <option value="{{ $valor }}" @selected(old('tamano_hoja', $configuracion->tamano_hoja) === $valor)>
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tamano_hoja')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Orientacion <span class="text-danger">*</span></label>
                                <select name="orientacion" class="form-select @error('orientacion') is-invalid @enderror">
                                    @foreach ($orientaciones as $valor => $texto)
                                        <option value="{{ $valor }}" @selected(old('orientacion', $configuracion->orientacion) === $valor)>
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('orientacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Color principal <span class="text-danger">*</span></label>
                            <input type="color" name="color_principal"
                                class="form-control form-control-color reportes-color @error('color_principal') is-invalid @enderror"
                                value="{{ old('color_principal', $configuracion->color_principal ?? '#635bff') }}">
                            @error('color_principal')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Titulo de encabezado</label>
                            <input type="text" name="titulo_encabezado" class="form-control @error('titulo_encabezado') is-invalid @enderror"
                                value="{{ old('titulo_encabezado', $configuracion->titulo_encabezado) }}"
                                placeholder="Ej: Proyecto Integrador Final">
                            @error('titulo_encabezado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Texto de pie</label>
                            <input type="text" name="texto_pie" class="form-control @error('texto_pie') is-invalid @enderror"
                                value="{{ old('texto_pie', $configuracion->texto_pie ?? 'Proyecto Integrador - Reporte generado automaticamente desde el sistema.') }}">
                            @error('texto_pie')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Logo y datos visibles</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Logo del PDF</label>
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text">Formatos permitidos: JPG, PNG, WEBP. Maximo 2MB.</div>
                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($configuracion->logo_path)
                            <div class="reportes-logo-preview mb-3">
                                <img src="{{ asset($configuracion->logo_path) }}" alt="Logo actual">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="quitar_logo" value="1" id="quitar_logo">
                                    <label class="form-check-label" for="quitar_logo">Quitar logo actual</label>
                                </div>
                            </div>
                        @endif

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="mostrar_logo" value="1" id="mostrar_logo"
                                @checked(old('mostrar_logo', $configuracion->mostrar_logo))>
                            <label class="form-check-label" for="mostrar_logo">Mostrar logo</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="mostrar_fecha" value="1" id="mostrar_fecha"
                                @checked(old('mostrar_fecha', $configuracion->mostrar_fecha))>
                            <label class="form-check-label" for="mostrar_fecha">Mostrar fecha de generacion</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="mostrar_generado_por" value="1" id="mostrar_generado_por"
                                @checked(old('mostrar_generado_por', $configuracion->mostrar_generado_por))>
                            <label class="form-check-label" for="mostrar_generado_por">Mostrar usuario generador</label>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line align-bottom me-1"></i>
                            Guardar configuracion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
