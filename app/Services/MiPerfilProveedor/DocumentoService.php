<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\DocumentoProveedor;
use App\Models\PerfilProveedor;
use App\Models\TipoDocumentoProveedor;
use App\Services\DocumentoProveedorService;
use Illuminate\Database\Eloquent\Collection;

class DocumentoService
{
    public function __construct(
        protected DocumentoProveedorService $documentoProveedorService
    ) {
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'documentos' => fn ($query) => $query->where('estado', true),
            'documentos.tipoDocumentoProveedor',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
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
        return $this->documentoProveedorService->createForPerfil(
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function actualizarActual(DocumentoProveedor $documentoProveedor, array $data): DocumentoProveedor
    {
        return $this->documentoProveedorService->updateForPerfil(
            $documentoProveedor,
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function eliminarActual(DocumentoProveedor $documentoProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $documentoProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->documentoProveedorService->bajaLogica($documentoProveedor);
    }
}
