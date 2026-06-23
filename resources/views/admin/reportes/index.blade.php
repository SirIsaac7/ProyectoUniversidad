@extends('layouts.app')

@section('title', 'Reportes')

@push('styles')
    <link href="{{ asset('assets/css/reportes.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="reportes-admin">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
                <div>
                    <h4 class="mb-1">Reportes</h4>
                    <p class="text-muted mb-0">Genera reportes PDF de los modulos principales del sistema.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('configurar reportes')
                        <a href="{{ route('reportes.configuracion') }}" class="btn btn-soft-info">
                            <i class="ri-file-settings-line align-bottom me-1"></i>
                            Configuracion PDF
                        </a>
                    @endcan

                    @can('crear reportes')
                        <a href="{{ route('reportes.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i>
                            Nuevo reporte
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card reporte-stat-card h-100">
                <div class="card-body">
                    <span class="reporte-stat-icon bg-primary-subtle text-primary"><i class="ri-file-chart-line"></i></span>
                    <div>
                        <p class="text-muted mb-1">Total reportes</p>
                        <h3 class="mb-0">{{ $resumen['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card reporte-stat-card h-100">
                <div class="card-body">
                    <span class="reporte-stat-icon bg-success-subtle text-success"><i class="ri-toggle-line"></i></span>
                    <div>
                        <p class="text-muted mb-1">Activos</p>
                        <h3 class="mb-0">{{ $resumen['activos'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card reporte-stat-card h-100">
                <div class="card-body">
                    <span class="reporte-stat-icon bg-info-subtle text-info"><i class="ri-bar-chart-grouped-line"></i></span>
                    <div>
                        <p class="text-muted mb-1">Con graficas</p>
                        <h3 class="mb-0">{{ $resumen['conGraficas'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card reporte-stat-card h-100">
                <div class="card-body">
                    <span class="reporte-stat-icon bg-warning-subtle text-warning"><i class="ri-image-line"></i></span>
                    <div>
                        <p class="text-muted mb-1">Con imagenes</p>
                        <h3 class="mb-0">{{ $resumen['conImagenes'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header align-items-center d-flex">
            <div class="flex-grow-1">
                <h5 class="card-title mb-1">Reportes configurados</h5>
                <p class="text-muted mb-0">Puedes editar la configuracion o generar el PDF actualizado.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Rango</th>
                            <th>Opciones</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportes as $reporte)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $reporte->nombre }}</div>
                                    <small class="text-muted">{{ $reporte->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>{{ $tipos[$reporte->tipo] ?? $reporte->tipo }}</td>
                                <td>
                                    @if ($reporte->fecha_inicio || $reporte->fecha_fin)
                                        {{ $reporte->fecha_inicio?->format('d/m/Y') ?? 'Inicio' }}
                                        -
                                        {{ $reporte->fecha_fin?->format('d/m/Y') ?? 'Hoy' }}
                                    @else
                                        <span class="text-muted">Todo el historial</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $reporte->incluir_graficas ? 'info' : 'secondary' }}-subtle text-{{ $reporte->incluir_graficas ? 'info' : 'secondary' }}">
                                        Graficas {{ $reporte->incluir_graficas ? 'si' : 'no' }}
                                    </span>
                                    <span class="badge bg-{{ $reporte->incluir_imagenes ? 'warning' : 'secondary' }}-subtle text-{{ $reporte->incluir_imagenes ? 'warning' : 'secondary' }}">
                                        Imagenes {{ $reporte->incluir_imagenes ? 'si' : 'no' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $reporte->estado ? 'success' : 'danger' }}-subtle text-{{ $reporte->estado ? 'success' : 'danger' }}">
                                        {{ $reporte->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>{{ $reporte->user?->name ?? 'Sistema' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('generar reportes')
                                            <a href="{{ route('reportes.pdf', $reporte) }}" class="btn btn-sm btn-soft-info" title="Generar PDF">
                                                <i class="ri-file-pdf-2-line"></i>
                                            </a>
                                        @endcan

                                        @can('editar reportes')
                                            <a href="{{ route('reportes.edit', $reporte) }}" class="btn btn-sm btn-soft-warning" title="Editar">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endcan

                                        @can('eliminar reportes')
                                            <form method="POST" action="{{ route('reportes.destroy', $reporte) }}" class="js-confirm-submit"
                                                data-confirm-title="Desactivar reporte"
                                                data-confirm-text="El reporte dejara de estar activo.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Desactivar">
                                                    <i class="ri-forbid-2-line"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    Aun no hay reportes configurados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $reportes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
