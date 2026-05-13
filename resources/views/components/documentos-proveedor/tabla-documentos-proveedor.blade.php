<?php

use App\Models\DocumentoProveedor;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estadoRevision = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    protected $listeners = [
        'confirmarEliminarDocumentoProveedor' => 'eliminar',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstadoRevision(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function sortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'ri-expand-up-down-line';
        }

        return $this->sortDirection === 'asc' ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line';
    }

    public function eliminar(int $documentoProveedorId): void
    {
        abort_unless(auth()->user()->can('eliminar documentos proveedor'), 403);

        $documentoProveedor = DocumentoProveedor::findOrFail($documentoProveedorId);
        $archivo = $documentoProveedor->archivo;

        $documentoProveedor->delete();

        if ($archivo && file_exists(public_path($archivo))) {
            unlink(public_path($archivo));
        }

        $this->dispatch('documento-proveedor-eliminado', message: 'Documento del proveedor eliminado correctamente.');
    }

    public function with(): array
    {
        $allowedSorts = ['id', 'estado_revision', 'fecha_revision', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';

        $documentosProveedor = DocumentoProveedor::query()
            ->with('perfilProveedor.user', 'tipoDocumentoProveedor')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('id', 'like', '%' . $this->search . '%')
                        ->orWhere('estado_revision', 'like', '%' . $this->search . '%')
                        ->orWhereHas('perfilProveedor', function ($perfilQuery) {
                            $perfilQuery->where('nombre_publico', 'like', '%' . $this->search . '%')
                                ->orWhereHas('user', function ($userQuery) {
                                    $userQuery->where('name', 'like', '%' . $this->search . '%')
                                        ->orWhere('email', 'like', '%' . $this->search . '%');
                                });
                        })
                        ->orWhereHas('tipoDocumentoProveedor', function ($tipoQuery) {
                            $tipoQuery->where('nombre', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estadoRevision !== '', function ($query) {
                $query->where('estado_revision', $this->estadoRevision);
            })
            ->orderBy($sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'documentosProveedor' => $documentosProveedor,
        ];
    }
};
?>

<div>
    @if (session('success'))
        <div class="d-none" id="documentos-proveedor-success-message" data-message="{{ session('success') }}"></div>
    @endif

    @if (session('error'))
        <div class="d-none" id="documentos-proveedor-error-message" data-message="{{ session('error') }}"></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" placeholder="Proveedor, tipo de documento o estado" wire:model.live.debounce.300ms="search">
        </div>

        <div class="col-md-4">
            <label class="form-label">Estado de revision</label>
            <select class="form-select" wire:model.live="estadoRevision">
                <option value="">Todos</option>
                <option value="pendiente">Pendientes</option>
                <option value="aprobado">Aprobados</option>
                <option value="rechazado">Rechazados</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Registros</label>
            <select class="form-select" wire:model.live="perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('id')">
                            ID <i class="{{ $this->sortIcon('id') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Proveedor</th>
                    <th>Tipo de documento</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('estado_revision')">
                            Revision <i class="{{ $this->sortIcon('estado_revision') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Archivo</th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('fecha_revision')">
                            Fecha de revision <i class="{{ $this->sortIcon('fecha_revision') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="btn btn-link p-0 text-reset fw-semibold" wire:click="sortBy('created_at')">
                            Fecha de creacion <i class="{{ $this->sortIcon('created_at') }} ms-1 small text-muted"></i>
                        </button>
                    </th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documentosProveedor as $documentoProveedor)
                    @php
                        $extensionArchivo = strtolower(pathinfo($documentoProveedor->archivo, PATHINFO_EXTENSION));
                        $esImagen = in_array($extensionArchivo, ['jpg', 'jpeg', 'png', 'webp'], true);
                    @endphp
                    <tr wire:key="documento-proveedor-{{ $documentoProveedor->id }}">
                        <td>{{ $documentoProveedor->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $documentoProveedor->perfilProveedor?->nombre_publico }}</div>
                            <small class="text-muted">{{ $documentoProveedor->perfilProveedor?->user?->email }}</small>
                        </td>
                        <td>{{ $documentoProveedor->tipoDocumentoProveedor?->nombre }}</td>
                        <td>
                            @if ($documentoProveedor->estado_revision === 'aprobado')
                                <span class="badge bg-success-subtle text-success">Aprobado</span>
                            @elseif ($documentoProveedor->estado_revision === 'rechazado')
                                <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ asset($documentoProveedor->archivo) }}" target="_blank" rel="noopener" title="Abrir archivo">
                                @if ($esImagen)
                                    <img
                                        src="{{ asset($documentoProveedor->archivo) }}"
                                        alt="Documento"
                                        class="rounded border object-fit-cover"
                                        style="width: 54px; height: 54px;"
                                    >
                                @else
                                    <span class="avatar-xs d-inline-flex align-items-center justify-content-center rounded bg-danger-subtle text-danger">
                                        <i class="ri-file-pdf-2-line fs-18"></i>
                                    </span>
                                @endif
                            </a>
                        </td>
                        <td>{{ optional($documentoProveedor->fecha_revision)->format('d/m/Y H:i') ?: 'Sin revision' }}</td>
                        <td>{{ optional($documentoProveedor->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="hstack gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-soft-info js-show-documento-proveedor"
                                    title="Ver detalle"
                                    data-bs-toggle="modal"
                                    data-bs-target="#documentoProveedorModal"
                                    data-proveedor="{{ $documentoProveedor->perfilProveedor?->nombre_publico }}"
                                    data-email="{{ $documentoProveedor->perfilProveedor?->user?->email }}"
                                    data-tipo="{{ $documentoProveedor->tipoDocumentoProveedor?->nombre }}"
                                    data-revision="{{ ucfirst($documentoProveedor->estado_revision) }}"
                                    data-observacion="{{ $documentoProveedor->observacion ?: 'Sin observacion' }}"
                                    data-fecha-revision="{{ optional($documentoProveedor->fecha_revision)->format('d/m/Y H:i') ?: 'Sin revision' }}"
                                    data-fecha-creacion="{{ optional($documentoProveedor->created_at)->format('d/m/Y H:i') }}"
                                    data-archivo="{{ asset($documentoProveedor->archivo) }}"
                                    data-es-imagen="{{ $esImagen ? '1' : '0' }}"
                                >
                                    <i class="ri-eye-line align-bottom"></i>
                                </button>

                                @can('editar documentos proveedor')
                                    <a href="{{ route('documentos-proveedor.edit', $documentoProveedor->id) }}" class="btn btn-sm btn-soft-warning" title="Editar">
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </a>
                                @endcan

                                @can('eliminar documentos proveedor')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-soft-danger js-delete-documento-proveedor-livewire"
                                        title="Eliminar"
                                        data-documento-proveedor-id="{{ $documentoProveedor->id }}"
                                        data-proveedor-nombre="{{ $documentoProveedor->perfilProveedor?->nombre_publico }}"
                                        data-tipo-documento-nombre="{{ $documentoProveedor->tipoDocumentoProveedor?->nombre }}"
                                    >
                                        <i class="ri-delete-bin-line align-bottom"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No se encontraron documentos del proveedor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $documentosProveedor->links() }}
    </div>

    <div class="modal fade" id="documentoProveedorModal" tabindex="-1" aria-labelledby="documentoProveedorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title" id="documentoProveedorModalLabel">Detalle del documento</h5>
                        <p class="text-muted mb-0 small">Revision y archivo presentado por el proveedor</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4 align-items-stretch">
                        <div class="col-lg-7">
                            <div class="h-100 border rounded p-3 text-center bg-light-subtle d-flex align-items-center justify-content-center" style="min-height: 360px;">
                                <img
                                    src=""
                                    alt="Vista previa del documento"
                                    class="img-fluid rounded d-none"
                                    style="max-height: 520px;"
                                    data-documento-modal="previewImagen"
                                >
                                <div class="py-5 d-none" data-documento-modal="previewArchivo">
                                    <span class="avatar-lg mx-auto d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger mb-3">
                                        <i class="ri-file-pdf-2-line display-6"></i>
                                    </span>
                                    <h5 class="mb-1">Archivo PDF</h5>
                                    <p class="text-muted mb-0">Abre el documento para revisarlo en una pestana nueva.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="h-100 border rounded p-3">
                                <div class="d-flex align-items-start gap-3 mb-4">
                                    <span class="avatar-sm d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary">
                                        <i class="ri-shield-user-line fs-20"></i>
                                    </span>
                                    <div>
                                        <small class="text-muted d-block">Proveedor</small>
                                        <div class="fw-semibold fs-15" data-documento-modal="proveedor"></div>
                                        <small class="text-muted" data-documento-modal="email"></small>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <small class="text-muted d-block">Tipo de documento</small>
                                    <div class="fw-semibold" data-documento-modal="tipo"></div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <div class="border rounded p-3 h-100">
                                            <small class="text-muted d-block mb-1">Revision</small>
                                            <span class="badge" data-documento-modal="revisionBadge"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="border rounded p-3 h-100">
                                            <small class="text-muted d-block mb-1">Creacion</small>
                                            <div class="fw-semibold small" data-documento-modal="fechaCreacion"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <small class="text-muted d-block mb-1">Fecha de revision</small>
                                            <div class="fw-semibold small" data-documento-modal="fechaRevision"></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <small class="text-muted d-block mb-1">Observacion</small>
                                    <div class="border rounded p-3 bg-light-subtle" style="min-height: 92px; white-space: pre-line;" data-documento-modal="observacion"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <a href="#" target="_blank" rel="noopener" class="btn btn-info" data-documento-modal="archivo">
                        <i class="ri-file-search-line align-bottom me-1"></i>
                        Ver archivo
                    </a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
