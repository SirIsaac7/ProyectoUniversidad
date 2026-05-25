<?php

namespace App\Services;

use App\Models\DocumentoProveedor;
use Illuminate\Http\UploadedFile;

class DocumentoProveedorService
{
    public function create(array $data): DocumentoProveedor
    {
        $documentoProveedor = DocumentoProveedor::where('perfil_proveedor_id', $data['perfil_proveedor_id'])
            ->where('tipo_documento_proveedor_id', $data['tipo_documento_proveedor_id'])
            ->first();

        if ($documentoProveedor) {
            return $this->update($documentoProveedor, [
                ...$data,
                'estado' => true,
            ]);
        }

        return DocumentoProveedor::create([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'tipo_documento_proveedor_id' => $data['tipo_documento_proveedor_id'],
            'archivo' => $this->storeFile($data['archivo'], 'documento-proveedor'),
            'estado_revision' => $data['estado_revision'],
            'observacion' => $data['observacion'] ?? null,
            'fecha_revision' => $this->resolveFechaRevision($data['estado_revision']),
            'estado' => $data['estado'] ?? true,
        ])->load('perfilProveedor.user', 'tipoDocumentoProveedor');
    }

    public function createForPerfil(int $perfilProveedorId, array $data): DocumentoProveedor
    {
        return $this->create([
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
            'estado_revision' => 'pendiente',
            'observacion' => null,
        ]);
    }

    public function update(DocumentoProveedor $documentoProveedor, array $data): DocumentoProveedor
    {
        $archivo = $documentoProveedor->archivo;

        if (! empty($data['archivo'])) {
            $this->deleteFile($documentoProveedor->archivo);
            $archivo = $this->storeFile($data['archivo'], 'documento-proveedor');
        }

        $documentoProveedor->update([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'tipo_documento_proveedor_id' => $data['tipo_documento_proveedor_id'],
            'archivo' => $archivo,
            'estado_revision' => $data['estado_revision'],
            'observacion' => $data['observacion'] ?? null,
            'fecha_revision' => $this->resolveFechaRevision($data['estado_revision'], $documentoProveedor),
            'estado' => $data['estado'] ?? $documentoProveedor->estado,
        ]);

        return $documentoProveedor->load('perfilProveedor.user', 'tipoDocumentoProveedor');
    }

    public function updateForPerfil(DocumentoProveedor $documentoProveedor, int $perfilProveedorId, array $data): DocumentoProveedor
    {
        abort_unless((int) $documentoProveedor->perfil_proveedor_id === $perfilProveedorId, 403);

        return $this->update($documentoProveedor, [
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
            'estado_revision' => 'pendiente',
            'observacion' => null,
        ]);
    }

    public function delete(DocumentoProveedor $documentoProveedor): void
    {
        $this->deleteFile($documentoProveedor->archivo);
        $documentoProveedor->delete();
    }

    public function toggleEstado(DocumentoProveedor $documentoProveedor): DocumentoProveedor
    {
        $documentoProveedor->update([
            'estado' => ! $documentoProveedor->estado,
        ]);

        return $documentoProveedor;
    }

    public function bajaLogica(DocumentoProveedor $documentoProveedor): DocumentoProveedor
    {
        $documentoProveedor->update([
            'estado' => false,
        ]);

        return $documentoProveedor;
    }

    protected function resolveFechaRevision(string $estadoRevision, ?DocumentoProveedor $documentoProveedor = null): ?string
    {
        if ($estadoRevision === 'pendiente') {
            return null;
        }

        if (
            $documentoProveedor
            && $documentoProveedor->estado_revision === $estadoRevision
            && $documentoProveedor->fecha_revision
        ) {
            return $documentoProveedor->fecha_revision->toDateTimeString();
        }

        return now()->toDateTimeString();
    }

    protected function storeFile(UploadedFile $file, string $nombre): string
    {
        $directorio = public_path('uploads/documentos-proveedor');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = now()->format('Ymd_His_u') . '_' . str()->slug($nombre) . '.' . $file->getClientOriginalExtension();

        $file->move($directorio, $nombreArchivo);

        return 'uploads/documentos-proveedor/' . $nombreArchivo;
    }

    protected function deleteFile(?string $ruta): void
    {
        if (! $ruta) {
            return;
        }

        $rutaCompleta = public_path($ruta);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }
}
