<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\PerfilProveedor;
use App\Models\PortafolioProveedor;
use App\Services\PortafolioProveedorService;

class PortafolioService
{
    public function __construct(
        protected PortafolioProveedorService $portafolioProveedorService
    ) {
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'portafolio' => fn ($query) => $query->where('estado', true),
            'portafolio.imagenes',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function crearActual(array $data): PortafolioProveedor
    {
        return $this->portafolioProveedorService->createForPerfil(
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function actualizarActual(PortafolioProveedor $portafolioProveedor, array $data): PortafolioProveedor
    {
        return $this->portafolioProveedorService->updateForPerfil(
            $portafolioProveedor,
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function eliminarActual(PortafolioProveedor $portafolioProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $portafolioProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->portafolioProveedorService->bajaLogica($portafolioProveedor);
    }
}
