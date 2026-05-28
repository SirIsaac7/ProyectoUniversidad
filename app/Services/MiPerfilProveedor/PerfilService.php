<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\PerfilProveedor;
use App\Services\PerfilProveedorService;

class PerfilService
{
    public function __construct(
        protected PerfilProveedorService $perfilProveedorService
    ) {
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with('user')
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function updatePerfilActual(array $data): PerfilProveedor
    {
        return $this->perfilProveedorService->updateDatosBasicos(
            $this->getPerfilActual(),
            $data
        );
    }
}
