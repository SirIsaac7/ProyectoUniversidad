@extends('layouts.app')

@section('title', 'Activity Logs')

@push('styles')
<style>
    .activity-detail-meta {
        background: var(--vz-light);
        border: 1px solid var(--vz-border-color);
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        height: 100%;
    }

    .activity-detail-meta .form-label {
        font-size: 0.75rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--vz-secondary-color);
        margin-bottom: 0.35rem;
    }

    .activity-detail-pre {
        background: var(--vz-light);
        border-radius: 0.75rem;
        padding: 1rem;
        min-height: 170px;
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent">
            <h4 class="mb-sm-0">Activity Logs</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Historial de actividad</h5>
            </div>
            <div class="card-body">
                @if ($activities->isEmpty())
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-4">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                <i class="ri-history-line"></i>
                            </div>
                        </div>
                        <h5 class="mb-2">No hay activity logs registrados</h5>
                        <p class="text-muted mb-0">Las acciones del sistema apareceran aqui automaticamente.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="tabla-activitylogs" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Log</th>
                                    <th>Evento</th>
                                    <th>Descripcion</th>
                                    <th>Usuario</th>
                                    <th>Subject</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activities as $activity)
                                    @php
                                        $descripcion = match ($activity->event) {
                                            'created' => 'Registro creado',
                                            'updated' => 'Registro actualizado',
                                            'deleted' => 'Registro eliminado',
                                            default => $activity->description,
                                        };
                                        $eventoClasses = match ($activity->event) {
                                            'created' => 'bg-success-subtle text-success',
                                            'updated' => 'bg-warning-subtle text-warning',
                                            'deleted' => 'bg-danger-subtle text-danger',
                                            default => 'bg-info-subtle text-info',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $activity->id }}</td>
                                        <td>{{ $activity->log_name }}</td>
                                        <td>
                                            <span class="badge {{ $eventoClasses }}">
                                                {{ $activity->event ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $descripcion }}</td>
                                        <td>{{ $activity->causer?->name ?? 'Sistema' }}</td>
                                        <td>
                                            {{ class_basename($activity->subject_type ?? '') }}
                                            @if($activity->subject_id)
                                                #{{ $activity->subject_id }}
                                            @endif
                                        </td>
                                        <td>{{ optional($activity->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-soft-info js-ver-cambios"
                                                title="Ver cambios"
                                                data-bs-toggle="modal"
                                                data-bs-target="#activityLogDetailModal"
                                                data-id="{{ $activity->id }}"
                                                data-log="{{ $activity->log_name }}"
                                                data-evento="{{ $activity->event ?? 'N/A' }}"
                                                data-descripcion="{{ $descripcion }}"
                                                data-usuario="{{ $activity->causer?->name ?? 'Sistema' }}"
                                                data-subject="{{ class_basename($activity->subject_type ?? '') }}@if($activity->subject_id) #{{ $activity->subject_id }}@endif"
                                                data-fecha="{{ optional($activity->created_at)->format('d/m/Y H:i:s') }}"
                                                data-old='@json($activity->properties["old"] ?? [])'
                                                data-attributes='@json($activity->properties["attributes"] ?? [])'
                                            >
                                                <i class="ri-eye-fill align-bottom"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="activityLogDetailModal" class="modal fade" tabindex="-1" aria-labelledby="activityLogDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light-subtle border-0">
                <h5 class="modal-title" id="activityLogDetailModalLabel">Detalle de actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">ID</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-id">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">Log</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-log">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">Evento</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-evento">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">Usuario</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-usuario">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">Subject</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-subject">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">Fecha</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-fecha">-</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="activity-detail-meta">
                            <label class="form-label fw-semibold">Descripcion</label>
                            <p class="text-body mb-0 fs-15" id="activity-detail-descripcion">-</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-soft-warning">
                                <h6 class="card-title mb-0">Valores anteriores</h6>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 small text-muted activity-detail-pre" id="activity-detail-old">Sin cambios anteriores</pre>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-soft-success">
                                <h6 class="card-title mb-0">Valores nuevos</h6>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 small text-muted activity-detail-pre" id="activity-detail-attributes">Sin valores nuevos</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/activitylogs.js') }}"></script>
@endpush
