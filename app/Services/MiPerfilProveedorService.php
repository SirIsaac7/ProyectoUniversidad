<?php

namespace App\Services;

use App\Models\DocumentoProveedor;
use App\Models\HorarioProveedor;
use App\Models\PerfilProveedor;
use App\Models\PortafolioProveedor;
use App\Models\ProveedorEspecialidad;
use App\Models\UbicacionProveedor;

class MiPerfilProveedorService
{
    public function __construct(
        protected PerfilProveedorService $perfilProveedorService,
        protected ProveedorEspecialidadService $proveedorEspecialidadService,
        protected HorarioProveedorService $horarioProveedorService,
        protected UbicacionProveedorService $ubicacionProveedorService,
        protected PortafolioProveedorService $portafolioProveedorService,
        protected DocumentoProveedorService $documentoProveedorService,
    ) {
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'user',
            'proveedorEspecialidades.especialidad.rubroTipoServicio.rubro',
            'proveedorEspecialidades.especialidad.rubroTipoServicio.tipoServicio',
            'horarios',
            'ubicacion',
            'portafolio.imagenes',
            'documentos.tipoDocumentoProveedor',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function updatePerfilActual(array $data): PerfilProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->perfilProveedorService->updateDatosBasicos($perfilProveedor, $data);
    }

    public function asignarEspecialidadActual(array $data): ProveedorEspecialidad
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->proveedorEspecialidadService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarEspecialidadActual(ProveedorEspecialidad $proveedorEspecialidad, array $data): ProveedorEspecialidad
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->proveedorEspecialidadService->updateForPerfil($proveedorEspecialidad, $perfilProveedor->id, $data);
    }

    public function crearHorarioActual(array $data): HorarioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->horarioProveedorService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarHorarioActual(HorarioProveedor $horarioProveedor, array $data): HorarioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->horarioProveedorService->updateForPerfil($horarioProveedor, $perfilProveedor->id, $data);
    }

    public function guardarUbicacionActual(array $data): UbicacionProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->ubicacionProveedorService->createOrUpdateForPerfil($perfilProveedor->id, $data);
    }

    public function crearPortafolioActual(array $data): PortafolioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->portafolioProveedorService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarPortafolioActual(PortafolioProveedor $portafolioProveedor, array $data): PortafolioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->portafolioProveedorService->updateForPerfil($portafolioProveedor, $perfilProveedor->id, $data);
    }

    public function subirDocumentoActual(array $data): DocumentoProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->documentoProveedorService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarDocumentoActual(DocumentoProveedor $documentoProveedor, array $data): DocumentoProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->documentoProveedorService->updateForPerfil($documentoProveedor, $perfilProveedor->id, $data);
    }
}
