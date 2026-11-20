<?php

namespace App\Services\Proveedores\MiPerfil;

use App\Models\DocumentoProveedor;
use App\Models\PerfilProveedor;
use App\Models\TipoDocumentoProveedor;
use App\Services\Notificaciones\NotificacionService;
use App\Services\Proveedores\DocumentoProveedorService;
use Illuminate\Database\Eloquent\Collection;

class DocumentoService
{
    public function __construct(
        protected DocumentoProveedorService $documentoProveedorService,
        protected NotificacionService $notificacionService
    ) {}

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'documentos' => fn ($query) => $query->where('estado', true),
            'documentos.tipoDocumentoProveedor',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function obtenerDatosVista(): array
    {
        $perfilProveedor = $this->getPerfilActual();
        $tiposDocumentoDisponibles = $this->getTiposDocumentoDisponibles();
        $documentos = $perfilProveedor->documentos;
        $documentosAprobados = $documentos->where('estado_revision', 'aprobado')->count();
        $documentosPendientes = $documentos->where('estado_revision', 'pendiente')->count();
        $documentosRechazados = $documentos->where('estado_revision', 'rechazado')->count();
        $ultimoDocumento = $documentos->sortByDesc('updated_at')->first();
        $carpetas = $this->prepararCarpetas($tiposDocumentoDisponibles, $documentos);

        return compact(
            'perfilProveedor',
            'tiposDocumentoDisponibles',
            'documentos',
            'documentosAprobados',
            'documentosPendientes',
            'documentosRechazados',
            'ultimoDocumento',
            'carpetas'
        );
    }

    public function getTiposDocumentoDisponibles(): Collection
    {
        return TipoDocumentoProveedor::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();
    }

    public function subirActual(array $data): DocumentoProveedor
    {
        $documento = $this->documentoProveedorService->createForPerfil(
            $this->getPerfilActual()->id,
            $data
        );

        $this->notificarAdministradores($documento);

        return $documento;
    }

    public function actualizarActual(DocumentoProveedor $documentoProveedor, array $data): DocumentoProveedor
    {
        $documento = $this->documentoProveedorService->updateForPerfil(
            $documentoProveedor,
            $this->getPerfilActual()->id,
            $data
        );

        $this->notificarAdministradores($documento, true);

        return $documento;
    }

    public function eliminarActual(DocumentoProveedor $documentoProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $documentoProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->documentoProveedorService->bajaLogica($documentoProveedor);
    }

    private function prepararCarpetas(Collection $tiposDocumentoDisponibles, $documentos): array
    {
        $colores = ['primary', 'warning', 'success', 'info', 'danger'];
        $icono = 'ri-folder-2-line';

        return $tiposDocumentoDisponibles
            ->values()
            ->map(function (TipoDocumentoProveedor $tipoDocumento, int $indice) use ($documentos, $colores, $icono) {
                return [
                    'id' => $tipoDocumento->id,
                    'nombre' => $tipoDocumento->nombre,
                    'descripcion' => $tipoDocumento->descripcion ?: ($tipoDocumento->obligatorio ? 'Documento obligatorio' : 'Documento opcional'),
                    'icono' => $icono,
                    'color' => $colores[$indice % count($colores)],
                    'documentos' => $documentos->where('tipo_documento_proveedor_id', $tipoDocumento->id),
                ];
            })
            ->all();
    }

    private function notificarAdministradores(DocumentoProveedor $documento, bool $actualizado = false): void
    {
        $documento->loadMissing('perfilProveedor.user', 'tipoDocumentoProveedor');

        $this->notificacionService->documentoPendienteParaAdministradores(
            proveedor: $documento->perfilProveedor?->nombre_publico
                ?? $documento->perfilProveedor?->user?->name
                ?? 'Un proveedor',
            tipoDocumento: $documento->tipoDocumentoProveedor?->nombre ?? 'sin tipo',
            actualizado: $actualizado,
            url: route('documentos-proveedor.index')
        );
    }
}
