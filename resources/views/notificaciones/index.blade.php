@extends('layouts.app')

@section('title', 'Centro de notificaciones')

@push('styles')
<link href="{{ asset('assets/css/notificacionesCentro.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
@php
    $estadoActual = $filtros['estado'] ?? 'todas';
    $tipoActual = $filtros['tipo'] ?? 'todos';
    $parametrosBase = request()->except(['page', 'estado', 'tipo']);
@endphp

<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
    <div>
        <h4 class="mb-1">Centro de notificaciones</h4>
        <p class="text-muted mb-0">Consulta y organiza todos los avisos asociados a tu cuenta.</p>
    </div>

    @can('gestionar mis notificaciones')
        <form method="POST" action="{{ route('notificaciones.leer-todas') }}" class="mt-3 mt-sm-0">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-soft-success" @disabled($resumen['no_leidas'] === 0)>
                <i class="ri-check-double-line align-bottom me-1"></i>
                Marcar todas como leidas
            </button>
        </form>
    @endcan
</div>

<div class="alert alert-info alert-dismissible fade show d-none js-centro-notificacion-nueva" role="alert">
    <i class="ri-notification-3-line me-1"></i>
    Recibiste una nueva notificacion.
    <a href="{{ request()->fullUrl() }}" class="alert-link ms-1">Actualizar listado</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card notification-summary-card h-100 mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-circle bg-primary-subtle text-primary"><i class="ri-notification-3-line fs-20"></i></span></span>
                <div><p class="text-muted mb-1">Total</p><h4 class="mb-0">{{ $resumen['total'] }}</h4></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card notification-summary-card h-100 mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-circle bg-danger-subtle text-danger"><i class="ri-mail-unread-line fs-20"></i></span></span>
                <div><p class="text-muted mb-1">No leidas</p><h4 class="mb-0">{{ $resumen['no_leidas'] }}</h4></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card notification-summary-card h-100 mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-circle bg-success-subtle text-success"><i class="ri-mail-check-line fs-20"></i></span></span>
                <div><p class="text-muted mb-1">Leidas</p><h4 class="mb-0">{{ $resumen['leidas'] }}</h4></div>
            </div>
        </div>
    </div>
</div>

<div class="card notification-center-card">
    <div class="card-body p-0">
        <div class="notification-center-layout">
            <aside class="notification-filter-panel border-end" aria-label="Filtros de notificaciones">
                <div class="p-3 p-lg-4">
                    <h5 class="fs-14 text-uppercase text-muted mb-3">Bandejas</h5>
                    <div class="nav nav-pills flex-column notification-filter-nav gap-1">
                        <a class="nav-link d-flex align-items-center {{ $estadoActual === 'todas' && $tipoActual === 'todos' ? 'active' : '' }}"
                           href="{{ route('notificaciones.index', array_merge($parametrosBase, ['estado' => 'todas', 'tipo' => 'todos'])) }}">
                            <i class="ri-inbox-archive-line me-2"></i><span>Todas</span>
                            <span class="badge ms-auto">{{ $resumen['total'] }}</span>
                        </a>
                        <a class="nav-link d-flex align-items-center {{ $estadoActual === 'no_leidas' && $tipoActual === 'todos' ? 'active' : '' }}"
                           href="{{ route('notificaciones.index', array_merge($parametrosBase, ['estado' => 'no_leidas', 'tipo' => 'todos'])) }}">
                            <i class="ri-mail-unread-line me-2"></i><span>No leidas</span>
                            <span class="badge ms-auto">{{ $resumen['no_leidas'] }}</span>
                        </a>
                        <a class="nav-link d-flex align-items-center {{ $estadoActual === 'leidas' && $tipoActual === 'todos' ? 'active' : '' }}"
                           href="{{ route('notificaciones.index', array_merge($parametrosBase, ['estado' => 'leidas', 'tipo' => 'todos'])) }}">
                            <i class="ri-mail-check-line me-2"></i><span>Leidas</span>
                            <span class="badge ms-auto">{{ $resumen['leidas'] }}</span>
                        </a>
                    </div>

                    <h5 class="fs-14 text-uppercase text-muted mt-4 mb-3">Categorias</h5>
                    <div class="nav nav-pills flex-column notification-filter-nav gap-1">
                        @foreach ($tipos as $clave => $meta)
                            <a class="nav-link d-flex align-items-center {{ $tipoActual === $clave ? 'active' : '' }}"
                               href="{{ route('notificaciones.index', array_merge($parametrosBase, ['estado' => $estadoActual, 'tipo' => $clave])) }}">
                                <i class="{{ $meta['icono'] }} text-{{ $meta['color'] }} me-2"></i>
                                <span>{{ $meta['nombre'] }}</span>
                                <span class="badge ms-auto">{{ $resumen['por_tipo'][$clave] ?? 0 }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <section class="notification-list-panel min-w-0" aria-labelledby="listadoNotificacionesTitulo">
                <div class="p-3 p-lg-4 border-bottom">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <button class="btn btn-soft-primary btn-icon d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosMovilesNotificaciones" aria-expanded="false" aria-controls="filtrosMovilesNotificaciones" title="Mostrar filtros">
                            <i class="ri-filter-3-line"></i>
                        </button>
                        <div>
                            <h5 class="mb-0" id="listadoNotificacionesTitulo">Mis notificaciones</h5>
                            <small class="text-muted">{{ $notificaciones->total() }} resultados encontrados</small>
                        </div>
                    </div>

                    <div class="collapse d-lg-none mb-3" id="filtrosMovilesNotificaciones">
                        <div class="border rounded p-2">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('notificaciones.index', ['estado' => 'todas']) }}" class="btn btn-sm btn-soft-primary">Todas</a>
                                <a href="{{ route('notificaciones.index', ['estado' => 'no_leidas']) }}" class="btn btn-sm btn-soft-danger">No leidas</a>
                                <a href="{{ route('notificaciones.index', ['estado' => 'leidas']) }}" class="btn btn-sm btn-soft-success">Leidas</a>
                                @foreach ($tipos as $clave => $meta)
                                    <a href="{{ route('notificaciones.index', ['tipo' => $clave]) }}" class="btn btn-sm btn-light">{{ $meta['nombre'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('notificaciones.index') }}" class="row g-2 align-items-end notification-search-form">
                        <input type="hidden" name="estado" value="{{ $estadoActual }}">
                        <input type="hidden" name="tipo" value="{{ $tipoActual }}">
                        <div class="col-12 col-xl">
                            <label for="buscarNotificacion" class="form-label visually-hidden">Buscar</label>
                            <div class="search-box">
                                <input type="search" class="form-control" id="buscarNotificacion" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Buscar por titulo o mensaje...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-auto">
                            <label for="ordenNotificacion" class="form-label visually-hidden">Orden</label>
                            <select class="form-select" id="ordenNotificacion" name="orden">
                                <option value="recientes" @selected(($filtros['orden'] ?? 'recientes') === 'recientes')>Mas recientes</option>
                                <option value="antiguas" @selected(($filtros['orden'] ?? '') === 'antiguas')>Mas antiguas</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-xl-auto">
                            <label for="porPaginaNotificacion" class="form-label visually-hidden">Por pagina</label>
                            <select class="form-select" id="porPaginaNotificacion" name="por_pagina">
                                @foreach ([10, 20, 50] as $cantidad)
                                    <option value="{{ $cantidad }}" @selected((int) ($filtros['por_pagina'] ?? 10) === $cantidad)>{{ $cantidad }} por pagina</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-filter-3-line me-1"></i>Filtrar</button>
                            <a href="{{ route('notificaciones.index') }}" class="btn btn-light" title="Limpiar filtros"><i class="ri-refresh-line"></i></a>
                        </div>
                    </form>
                </div>

                @can('gestionar mis notificaciones')
                    <form id="accionMasivaNotificaciones" method="POST" action="{{ route('notificaciones.accion-masiva') }}" class="d-none">
                        @csrf
                        @method('PATCH')
                    </form>
                    <div class="notification-bulk-bar px-3 px-lg-4 py-2 border-bottom d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted me-auto"><strong class="js-notificaciones-seleccionadas">0</strong> seleccionadas</span>
                        <select name="accion" form="accionMasivaNotificaciones" class="form-select form-select-sm notification-bulk-select" aria-label="Accion masiva">
                            <option value="">Selecciona una accion</option>
                            <option value="leer">Marcar como leidas</option>
                            <option value="no_leer">Marcar como no leidas</option>
                            @can('eliminar mis notificaciones')
                                <option value="eliminar">Eliminar</option>
                            @endcan
                        </select>
                        <button type="submit" form="accionMasivaNotificaciones" class="btn btn-sm btn-soft-primary js-aplicar-accion" title="Aplicar la accion a las notificaciones seleccionadas" disabled>Aplicar</button>
                    </div>
                @endcan

                @error('notificaciones')
                    <div class="alert alert-danger m-3 mb-0">{{ $message }}</div>
                @enderror
                @error('accion')
                    <div class="alert alert-danger m-3 mb-0">{{ $message }}</div>
                @enderror

                <div class="notification-table-wrap">
                    <table class="table align-middle mb-0 notification-table">
                        <thead>
                            <tr>
                                @can('gestionar mis notificaciones')
                                    <th class="notification-check-column">
                                        <input class="form-check-input js-seleccionar-todas-notificaciones" type="checkbox" aria-label="Seleccionar todas las notificaciones de esta pagina">
                                    </th>
                                @endcan
                                <th>Notificacion</th>
                                <th>Categoria</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notificaciones as $notificacion)
                                @php
                                    $datos = is_array($notificacion->data) ? $notificacion->data : [];
                                    $tipo = $datos['tipo'] ?? 'info';
                                    $meta = $tipos[$tipo] ?? $tipos['info'];
                                    $urlDestino = $datos['url'] ?? null;
                                @endphp
                                <tr class="{{ $notificacion->read_at ? '' : 'notification-unread' }}">
                                    @can('gestionar mis notificaciones')
                                        <td class="notification-check-column" data-label="Seleccionar">
                                            <input class="form-check-input js-notificacion-checkbox" type="checkbox" name="notificaciones[]" value="{{ $notificacion->id }}" form="accionMasivaNotificaciones" aria-label="Seleccionar {{ $datos['titulo'] ?? 'notificacion' }}">
                                        </td>
                                    @endcan
                                    <td data-label="Notificacion">
                                        <div class="d-flex align-items-start gap-3 notification-message-cell">
                                            <span class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title rounded-circle bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}">
                                                    <i class="{{ $meta['icono'] }} fs-18"></i>
                                                </span>
                                            </span>
                                            <div class="min-w-0">
                                                <span class="fw-semibold text-body text-break">{{ $datos['titulo'] ?? 'Notificacion' }}</span>
                                                <p class="text-muted mb-0 mt-1 notification-message text-break">{{ $datos['mensaje'] ?? 'Sin detalle adicional.' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Categoria"><span class="badge bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}">{{ $meta['nombre'] }}</span></td>
                                    <td data-label="Estado">
                                        @if ($notificacion->read_at)
                                            <span class="badge bg-success-subtle text-success">Leida</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Nueva</span>
                                        @endif
                                    </td>
                                    <td data-label="Fecha">
                                        <span title="{{ $notificacion->created_at?->format('d/m/Y H:i:s') }}">{{ $notificacion->created_at?->diffForHumans() }}</span>
                                    </td>
                                    <td class="text-end notification-actions" data-label="Acciones">
                                        <div class="d-inline-flex gap-1">
                                            @if ($urlDestino)
                                                <a href="{{ $urlDestino }}" class="btn btn-sm btn-soft-info btn-icon" title="Ver informacion relacionada" aria-label="Ver informacion relacionada">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            @endif
                                            @can('gestionar mis notificaciones')
                                                @if ($notificacion->read_at)
                                                    <form method="POST" action="{{ route('notificaciones.no-leer', $notificacion->id) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-soft-warning btn-icon" title="Marcar como no leida"><i class="ri-mail-unread-line"></i></button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('notificaciones.leer', $notificacion->id) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-soft-success btn-icon" title="Marcar como leida"><i class="ri-mail-check-line"></i></button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('eliminar mis notificaciones')
                                                <form method="POST" action="{{ route('notificaciones.destroy', $notificacion->id) }}" class="js-eliminar-notificacion">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger btn-icon" title="Eliminar"><i class="ri-delete-bin-6-line"></i></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('gestionar mis notificaciones') ? 6 : 5 }}" class="text-center py-5">
                                        <div class="avatar-md mx-auto mb-3"><span class="avatar-title rounded-circle bg-primary-subtle text-primary"><i class="ri-notification-off-line fs-24"></i></span></div>
                                        <h5>No se encontraron notificaciones</h5>
                                        <p class="text-muted mb-2">Prueba cambiando los filtros o el texto de busqueda.</p>
                                        <a href="{{ route('notificaciones.index') }}" class="btn btn-sm btn-soft-primary">Limpiar filtros</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($notificaciones->hasPages())
                    <div class="p-3 p-lg-4 border-top">{{ $notificaciones->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/notificacionesCentro.js') }}"></script>
@endpush
