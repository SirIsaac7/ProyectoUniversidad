<?php

namespace App\Services;

use App\Models\TipoDocumentoProveedor;

class TipoDocumentoProveedorService
{
    public function create(array $data): TipoDocumentoProveedor
    {
        return TipoDocumentoProveedor::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'obligatorio' => $data['obligatorio'],
            'estado' => $data['estado'],
        ]);
    }

    public function update(TipoDocumentoProveedor $tipoDocumentoProveedor, array $data): TipoDocumentoProveedor
    {
        $tipoDocumentoProveedor->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'obligatorio' => $data['obligatorio'],
            'estado' => $data['estado'],
        ]);

        return $tipoDocumentoProveedor;
    }

    public function toggleEstado(TipoDocumentoProveedor $tipoDocumentoProveedor): TipoDocumentoProveedor
    {
        $tipoDocumentoProveedor->update([
            'estado' => ! $tipoDocumentoProveedor->estado,
        ]);

        return $tipoDocumentoProveedor;
    }
}
