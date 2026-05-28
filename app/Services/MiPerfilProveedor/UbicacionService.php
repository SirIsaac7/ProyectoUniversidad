<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\PerfilProveedor;
use App\Models\UbicacionProveedor;
use App\Services\UbicacionProveedorService;

class UbicacionService
{
    public function __construct(
        protected UbicacionProveedorService $ubicacionProveedorService
    ) {
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with('ubicacion')
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function guardarActual(array $data): UbicacionProveedor
    {
        return $this->ubicacionProveedorService->createOrUpdateForPerfil(
            $this->getPerfilActual()->id,
            $data
        );
    }
}
