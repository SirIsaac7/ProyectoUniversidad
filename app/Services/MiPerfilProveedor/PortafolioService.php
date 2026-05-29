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
            'portafolio' => fn ($query) => $query->latest(),
            'portafolio.imagenes',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function obtenerDatosVista(): array
    {
        $perfilProveedor = $this->getPerfilActual();
        $trabajos = $perfilProveedor->portafolio;
        $trabajosActivos = $trabajos->where('estado', true)->values();
        $trabajosInactivos = $trabajos->where('estado', false)->values();
        $imagenesTotales = $trabajosActivos->sum(fn ($trabajo) => $trabajo->imagenes->where('estado', true)->count());
        $trabajosConFecha = $trabajosActivos->whereNotNull('fecha_trabajo')->count();
        $ultimoTrabajo = $trabajosActivos->sortByDesc('updated_at')->first();

        return compact(
            'perfilProveedor',
            'trabajos',
            'trabajosActivos',
            'trabajosInactivos',
            'imagenesTotales',
            'trabajosConFecha',
            'ultimoTrabajo'
        );
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

    public function activarActual(PortafolioProveedor $portafolioProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $portafolioProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->portafolioProveedorService->activar($portafolioProveedor);
    }
}
