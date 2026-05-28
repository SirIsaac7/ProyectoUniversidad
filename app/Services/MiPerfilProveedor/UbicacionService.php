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

    public function obtenerDatosVista(): array
    {
        $perfilProveedor = $this->getPerfilActual();
        $ubicacion = $perfilProveedor->ubicacion;

        return [
            'perfilProveedor' => $perfilProveedor,
            'ubicacion' => $ubicacion,
            'tieneUbicacion' => (bool) $ubicacion,
            'radioCobertura' => $ubicacion?->radio_cobertura_km ?: 1,
        ];
    }

    public function guardarActual(array $data): UbicacionProveedor
    {
        return $this->ubicacionProveedorService->createOrUpdateForPerfil(
            $this->getPerfilActual()->id,
            $data
        );
    }
}
